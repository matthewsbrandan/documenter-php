<?php
  header("Content-Type: application/json");

  if(!isset($_POST)){
    echo json_encode([
      'result' => false,
      'response' => 'Método HTTP Inválido'
    ]);
    exit();
  }
  if(
    !isset($_POST['path']) ||
    !isset($_POST['body']) ||
    !isset($_POST['remote_address'])
  ){
    echo json_encode([
      'result' => false,
      'response' => 'Requisição Inválida'
    ]);
    exit();
  }

  include_once __DIR__."/../vendor/autoload.php";

  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
  $dotenv->load();

  $documenter_php_secret = $_ENV['DOCUMENTER_PHP_SECRET']; 

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

  $data = [
    'path' => $_POST['path'],
    'body' => json_encode($_POST['body'])
  ];
  $address = $_POST['remote_address'];

  [$res, $err, $real] = requestHTTP('POST', $address, $data, (object)['header' => [
    "documenter-php-secret: $documenter_php_secret"
  ]]);

  echo $real;
  exit();