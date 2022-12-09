<?php
  session_start();
  
  #region DECLARE FUNCTIONS
  require_once './helpers.php';
  $base_url = str_contains($_SERVER['HTTP_REFERER'],'index.php') ?
    str_replace('index.php','',$_SERVER['HTTP_REFERER']) :
    $_SERVER['HTTP_REFERER'];    
  $saveInPath = $_POST['nameapp'] ?? strtotime(date('Y-m-d H:i:s'));

  $_SESSION['nameapp'] = $saveInPath;
  $_SESSION['path'] = $_POST['path'] ?? null;
  $_SESSION['map'] = $_POST['map'];

  function removeAsteriskFromComments($str){
    $arr = explode('*', $str);
    if(count($arr) == 1) return $arr[0];
    $firstPart = array_splice($arr, 0, 1)[0];
    $rest = implode('*', $arr);

    return $firstPart . $rest;
  }
  function handleComment($file, $num_line){
    #region VALIDATE IF EXISTS COMMENTS
    $index = $num_line - 1;
    if($index == 0) return null;

    $prev_line = $file[$index - 1];
    if(!str_contains($prev_line,'*/')) return null;
    $end_index = $index - 1;

    $temp_index = $end_index;
    do{
      $temp_index--;
      if($temp_index < 0) return null;
    }while(!str_contains($file[$temp_index],'/**'));
    $start_index = $temp_index;
    #endregion VALIDATE IF EXISTS COMMENTS    
    $area = array_map(function($line){
      return removeAsteriskFromComments($line);
    }, array_slice($file, $start_index + 1, $end_index - $start_index - 1));
    
    $content = (object)[
      'description' => null,
      'route_name' => null,
      'return_type' => null,
      'http'=> null,
      'params' => null,
      'return' => null
    ];
    $handleParamsAndReturn = (object)[
      'on' => null,
      'params' => (object)['start' => null, 'end' => null],
      'return' => (object)['start' => null, 'end' => null]
    ];

    foreach($area as $i_area => $line){
      #region HANDLE BLOCK PARAM OR RETURN
      if($handleParamsAndReturn->on !== null){
        if($handleParamsAndReturn->on == '@params'){
          if(str_contains($line, '@endparams')){
            $handleParamsAndReturn->on = null;
            $handleParamsAndReturn->params->end = $i_area;
          }
        }
        if($handleParamsAndReturn->on == '@return'){
          if(str_contains($line, '@endreturn')){
            $handleParamsAndReturn->on = null;
            $handleParamsAndReturn->return->end = $i_area;
          }
        }
        continue;
      }
      if(str_contains($line, '@params')){
        $handleParamsAndReturn->on = "@params";
        $handleParamsAndReturn->params->start = $i_area;
        continue;
      }
      if(str_contains($line, '@return') && !str_contains($line, '@return_type')){
        $handleParamsAndReturn->on = "@return";
        $handleParamsAndReturn->return->start = $i_area;
        continue;
      }
      #endregion HANDLE BLOCK PARAM OR RETURN
      if(str_contains($line, '@description')){
        $content->description = trim(str_replace('@description', '', $line));
        continue;
      }
      if(str_contains($line, '@route_name')){
        $content->route_name = trim(str_replace('@route_name', '', $line));
        continue;
      }
      if(str_contains($line, '@return_type')){
        $content->return_type = trim(str_replace('@return_type', '', $line));
        continue;
      }
      if(str_contains($line, '@http')){
        $content->http = trim(str_replace('@http', '', $line));
        continue;
      } 
    }

    #region HANDLE PARAMS AND RETURNS
    if(
      $handleParamsAndReturn->params->start !== null && 
      $handleParamsAndReturn->params->end !== null
    ){
      $str_param = trim(implode('', array_slice(
        $area,
        $handleParamsAndReturn->params->start + 1,
        $handleParamsAndReturn->params->end - $handleParamsAndReturn->params->start - 1
      )));
      $content->params = json_decode($str_param);
    }
    if(
      $handleParamsAndReturn->return->start !== null && 
      $handleParamsAndReturn->return->end !== null
    ){
      $str_return = trim(implode('', array_slice(
        $area,
        $handleParamsAndReturn->return->start + 1,
        $handleParamsAndReturn->return->end - $handleParamsAndReturn->return->start - 1
      )));
      $content->return = json_decode($str_return);
    }
    #endregion HANDLE PARAMS AND RETURNS

    return $content;
  }
  function mapControllers($dir){
    global $saveInPath;
    global $base_url;

    $watch = [];
    $sub_directories = [];
    #region LOAD FILES AND SUBDIRECTORIES TO WATCH
    if($dh = opendir($dir)) {
      while(($file = readdir($dh)) !== false) {
        if(in_array($file,['.','..'])) continue;
        $path = $dir . '\\' . $file;
        if(is_dir($path)) $sub_directories[] = (object)[
          'dirname' => $file,
          'path' => $path
        ];
        else $watch[] = (object)[
          'filename' => $file,
          'path' => $path
        ];
      }
      closedir($dh);
    }
    else redirectBackWithMessage([
      'title' => 'Erro ao abrir <b>Controladores</b>',
      'message' => 'Não foi possível abrir a pasta de <b>Controladores</b>',
      'type' => 'danger',
    ]);
    #endregion LOAD FILES AND SUBDIRECTORIES TO WATCH
    $resume = [];
    foreach($watch as $file){
      $opened = file(
        $file->path
      );

      $filename = str_replace('.php', '', $file->filename);
      $file_description = null;

      $functions = [];
      foreach($opened as $num_line => $line){
        $real_num_line = $num_line + 1;
        if(str_contains($line, "class $filename")){
          $comments = handleComment(
            $opened, $real_num_line
          );
          if(isset($comments->description)) $file_description = $comments->description;
          continue;
        }
        if(str_contains($line, ' function ')){
          $line = trim($line);
          $splited = explode('function',$line);
          if(count($splited) != 2) continue;

          $accessor = trim($splited[0]);
          if(!in_array($accessor,['public','private','protected'])) $accessor = null;
          
          $desc = trim($splited[1]);
          $index = strpos($desc, '(');
          if($index === false) continue;
          $function_name = trim(substr($desc, 0, $index));

          $params = null;
          $final_index = strpos($desc, ')');
          if($final_index !== false){
            $params = trim(substr($desc, $index + 1, $final_index - $index - 1));
            if(strlen($params) == 0) $params = null;
            $params = array_map(function($item){ return trim($item); }, explode(',', $params));
          }
        
          $comments = handleComment(
            $opened, $real_num_line
          );

          $functions[] = (object)[
            'name' => $function_name,
            'access_modifier' => $accessor,
            'params' => $params,
            'line' => $real_num_line,
            'content' =>  $comments
          ];
          if(!isset($resume[$file->filename]['functions'])) $resume[$file->filename]['functions'] = [];
          $resume[$file->filename]['name'] = str_replace('.php','', $file->filename);
          $resume[$file->filename]['functions'][]= $function_name;
        }
      }

      $handled= [
        'name' => $filename,
        'description' => $file_description,
        'functions' => $functions
      ];
      save($saveInPath, "controllers-$filename.json", json_encode($handled));
    }
    // LIDAR COM SUBDIRETÓRIOS
    // $sub_directories
    return [
      $base_url . "public/files/$saveInPath/controllers.json",
      $resume
    ];
  }
  #endregion DECLARE FUNCTIONS

  #region VALIDATION
  if(!isset($_POST) || !isset($_POST['path'])) redirectBackWithMessage([
    'type' =>  'danger',
    'message' => 'A requisição não veio num formato esperado. É obrigatório que seja uma requisição <b>POST</b>, enviando a <b>path</b> do destino.',
    'title' => 'Erro de requisição'
  ]);
  if(!isset($_POST['map']) || count($_POST['map']) == 0) redirectBackWithMessage([
    'type' =>  'danger',
    'message' => 'Você deve escolher no <b>mínimo 1</b> item para ser mapeado.',
    'title' => 'Erro de mapeamento'
  ]);
  $dir = $_POST['path'];
  if(!is_dir($dir)) redirectBackWithMessage([
    'title' => 'Erro de destino',
    'message' => 'O endereço de destino não é um diretório válido',
    'type' => 'danger',
  ]);
  #endregion VALIDATION

  $map = (object)[
    'controllers' => (object)[
      'name' => 'Controllers',
      'active' => in_array('controllers', $_POST['map']),
      'files' => []
    ],
    'repositories' => (object)[
      'name' => 'Repositories',
      'active' => in_array('repositories', $_POST['map']),
      'files' => []
    ],
    'models' => (object)[
      'name' => 'Models',
      'active' => in_array('models', $_POST['map']),
      'files' => []
    ],
    'commands' => (object)[
      'name' => 'Commands',
      'active' => in_array('commands', $_POST['map']),
      'files' => []
    ],
    'observers' => (object)[
      'name' => 'Observers',
      'active' => in_array('observers', $_POST['map']),
      'files' => []
    ],
    'services' => (object)[
      'name' => 'Services',
      'active' => in_array('services', $_POST['map']),
      'files' => []
    ],
    'routes' => (object)[
      'name' => 'Routes',
      'active' => in_array('routes', $_POST['map']),
      'files' => []
    ],
    'views' => (object)[
      'views' => 'Views',
      'active' => in_array('views', $_POST['map']),
      'files' => []
      ]
  ];

  $message = null;
  if($map->controllers->active){
    $dir_controllers = $dir . (
      substr($dir,-1) == '\\' ? '': '\\'
    ) . 'app\Http\controllers';
    if(!is_dir($dir_controllers)) redirectBackWithMessage([
      'title' => 'Erro de mapeamento',
      'message' => 'Não foi possível mapear o <b>Controllador</b> pois a pasta não está na localização esperada',
      'type' => 'danger',
    ]);

    [$file_generated,$files] = mapControllers($dir_controllers);
    $map->controllers->files = $files;

    $message = 'Documentação dos <b>Controladores</b> gerada com sucesso!';
    // if($file_generated) $message.= "<br/><a target=\"_blank\" href=\"$file_generated\">controller.json</a>"; 
  }

  save($saveInPath, "map.json", json_encode($map));
  $file_generated = $base_url . "public/files/$saveInPath/map.json";
  if(!$message) $message = "Mapeamento gerado com sucesso!";
  $message.= "<br/><a target=\"_blank\" href=\"$file_generated\">map.json</a>";

  redirectBackWithMessage([
    'title' => 'Gerado com sucesso',
    'message' => $message,
    'type' => 'success',
  ]);