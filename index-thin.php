<?php

require_once "vendor/autoload.php";
//require_once "src/maverickChunker-php5.php";
require_once "src/maverickChunker-thin.php";
//require_once "src/maverickChunker.php";

use GuzzleHttp\Client;
use Dotenv\Dotenv;

class chunkHandler {
    
    private $apiConf;
    private $articleId;

    function __construct() {
        // Looing for .env at the root directory
        $dotenv = Dotenv::createMutable(__DIR__);
        $dotenv->load();

        $this->articleId = $_REQUEST["article_id"];
        $this->apiConf = [
            'path' => $_ENV['API_PATH'],
            'token' => $_ENV['API_TOKEN']
        ];
    }
    
    function make_request() 
    {
        if(!$this->articleId){
            echo json_encode(['sts' => 'error', 'message'=>'Bad Request']);
        }else{

            $body = $this->get_raw_source();
            //echo $body.'<hr>';

            $body = json_decode($body, true);
            //print_r($body);

            if(count($body) <= 0){
                echo json_encode(['sts' => 'error', 'message'=>'Not found']);
            }else{
                $chunker = new maverickChunker($body);

                //populate all content into a single text
                /*$content = $body->news_content;
                foreach($body->news_paging as $paging){
                    $content .= html_entity_decode($paging->content);
                }*/
                //return (json_decode($chunker->explode_content()));
                
                return ($chunker->parseNews($body));
                
            }
        }
    }

    function get_raw_source() 
    {
        $client = new Client();

        $url = $this->apiConf['path'].'/news/'
        . $this->articleId
        . '/&token='
        . $this->apiConf['token'];

        $response = $client->request('GET', $url);
        
        return $response->getBody();

    }
}

header('Content-Type: application/json; charset=utf-8');
$exec = new chunkHandler();
print_r($exec->make_request());

