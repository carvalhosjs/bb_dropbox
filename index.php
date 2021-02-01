<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once "vendor/autoload.php";

    use BBDropbox\Core\Dropbox;


    $token= "";
    $userToken= "";

//     $data = (new Dropbox())->auth($token, $userToken)
//         ->listFolder("/digital/logs");
//
//    $data = (new Dropbox())->auth($token, $userToken)
//            ->downloadZip("/digital/logs", "teste.zip");

     $data = (new Dropbox())->auth($token, $userToken)
            ->memberInfo();


    echo "<pre>";
    var_dump($data);
    echo "</pre>";
