<?php
$url = isset($_REQUEST["path"]) ? $_REQUEST["path"] : false;

if($url){
    $ext = substr($url, strlen($url)-3, 3);
    if($ext=='.js'){
        header("Content-type: text/javascript", true);
    }else{
        header("Content-type: text/css", true);
    }
    $asset = file_get_contents($url);
    echo $asset;
}
