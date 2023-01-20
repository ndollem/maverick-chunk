<?php
#$url = isset($_REQUEST["path"]) ? $_REQUEST["path"] : false;
$url = 'https://www.trstdly.com/recommendation?api_component=true&news_tags=actor,snow,marvel,hawkeye,incident&news_id=487956';
if($url){
    $asset = file_get_contents($url);
    echo $asset;
}
