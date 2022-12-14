<?php
  if(!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
      return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
  }
  function dd($arr){
    echo "<pre>";
    var_dump($arr);
    die;
  }
  function redirectBackWithMessage($message, $address = '../index.php'){
    $_SESSION['message'] = (object) $message;
    return header('Location: ' . $address);
  }
  function requestHTTP($method, $param, $data = "", $options = null){
    if(!in_array($method, ['GET','POST','PUT','\DELETE'])) throw new Exception(
      "Método HTTP inválido"
    );

    #region HANDLE OPTIONS
    if(!$options) $options = (object)[];
    $json_decode = $options->json_decode ?? true;
    #endregion HANDLE OPTIONS
    #region HANDLE HEADER
    $header = ["Content-Type: application/json"];
    if(isset($options->header) && is_array($options->header)) $header = [
      ...$header,
      ...$options->header
    ];
    #endregion HANDLE HEADER

    $curl = curl_init();
    
    curl_setopt_array($curl, [
      CURLOPT_URL => $param,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_POSTFIELDS => is_string($data) ? $data : json_encode($data),
      CURLOPT_HTTPHEADER => $header
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    $formated = $response;
    if($json_decode) $formated = json_decode($response);
    
    return [$formated, $err, $response];
  }
  function save($dir, $file, $content){
    #region REMOTE SAVE
    if(isset($_SESSION) && isset($_SESSION['remote_address'])){
      $documenter_php_secret = $_ENV['DOCUMENTER_PHP_SECRET']; 
      $address = $_SESSION['remote_address'];

      if(!isset($_SESSION['SAVE_ERRORS'])) $_SESSION['SAVE_ERRORS'] = [];
      if(strpos($address, 'http://') !== false ||
        strpos($address, 'https://') !== false
      ){
        $data = ['path' => [], 'body' => $content];

        #region HANDLE PATH
        global $saveInPath;
        if(is_array($dir)){
          if($dir[0] == $saveInPath) array_shift($dir);
          $data['path'] = $dir;
        }else if($dir != $saveInPath){
          $slashe = strpos($dir,'/') !== false ? '/' : '\\';
          $data['path'] = explode($slashe, $dir);
        }
        $data['path'][] = $file;
        #endregion HANDLE PATH
        if(!isset($_SESSION['REQUEST_QUEUE'])) $_SESSION['REQUEST_QUEUE'] = [];
        $_SESSION['REQUEST_QUEUE'][] = [
          'file' => $file,
          'path' => $data['path'],
          'location' => "public/files/" . (is_array($dir) ? implode('/', $dir) : $dir) . "/$file"
        ];
        // [$res, $err, $real] = requestHTTP('POST', $address, $data, (object)['header' => [
        //   "documenter-php-secret: $documenter_php_secret"
        // ]]);

        // if(is_string($err)) $err = trim($err);
        // if($err || !$res){
        //   $data = (object)[
        //     'result' => false,
        //     'response' => 'Houve um erro ao enviar a requisição do ' . implode('/', $data['path']) . (is_string($err) ? 
        //       '. ' . $err : ''
        //     ),
        //     'file' => $data['path'],
        //     'res' => $res,
        //     'err' => $err
        //   ];
        //   if($res == null && !$err){ echo $real; dd($res, $err, $real); }
        //   $_SESSION['SAVE_ERRORS'][] = $data;
        //   return $data;
        // }
        // if(!$res->result) $_SESSION['SAVE_ERRORS'][] = (object)[((array) $res) + [
        //   'file' => $data['path']
        // ]];
        // return $res;
      }
      else{
        // CRIAR LÓGICA PARA CAMINHO CUSTOMIZADO
        dd('Em desenvolvimento');
      }
    }
    #endregion REMOTE SAVE

    if(is_array($dir)){
      $path = "../public/files";
      foreach($dir as $d){
        $path.= "/$d";
        if(!is_dir($path)) mkdir($path);
      }
    }else{
      $path = "../public/files/$dir";
  
      if(!is_dir($path)) mkdir($path);
    }
    $path.= "/$file";

    $fp = fopen($path, "w+");  
    fwrite($fp, $content);
    fclose($fp);
  }