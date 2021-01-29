<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once "vendor/autoload.php";

    use BBDropbox\Core\Dropbox;

//    $url = "https://api.dropboxapi.com/2/files/list_folder";
//    $url2 = "https://content.dropboxapi.com/2/files/download";


//     $data = (new Dropbox($url))->listFolder("/digital/historico/4517/aso");
//     $path = __DIR__ . '/../../novo/teste/224401.json';
//     $data = (new Dropbox($url2))->downloadFile("/digital/historico/4517/aso/224401.json", $path);

//    $url3 = "https://content.dropboxapi.com/2/files/download_zip";
//    $path2 = __DIR__ . '/../../novo/teste/funcionarios.zip';
//    $data = (new Dropbox($url3))->downloadZip("/digital/historico/4517/aso", $path2, true, '4517');

//
//    $url4 = "https://content.dropboxapi.com/2/files/upload";
//    $disk = __DIR__ . '/../../novo/teste/224401.json';
//    $cloud = '/teste/teste.json';
//    $data = (new Dropbox($url4))->upload($disk, $cloud);


//    $url5 = "https://api.dropboxapi.com/2/files/delete_v2";
//    $cloud = "/teste/teste.json";
//    $data = (new Dropbox($url5))->deleteFile($cloud);

//
//    $url6 = "https://content.dropboxapi.com/2/files/export";
//    $cloud = "/teste/95.jpg";
//    $data = (new Dropbox($url6))->exportFile($cloud);


//    $url7 = "https://api.dropboxapi.com/2/files/search_v2";
//    $query = "95";
//    $cloud = "/teste";
//    $data = (new Dropbox($url7))->searchFileFolder($query, $cloud);


//    echo "<pre>";
//    var_dump($data);
//    echo "</pre>";
