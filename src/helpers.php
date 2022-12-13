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
  function save($dir, $file, $content){
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