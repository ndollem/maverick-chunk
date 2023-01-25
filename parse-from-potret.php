<?php

require_once "vendor/autoload.php";
//require_once "src/maverickChunker-php5.php";
require_once "src/maverickChunker.php";

use GuzzleHttp\Client;
use Dotenv\Dotenv;

class chunkHandler {
    
    private $apiConf;
    private $slug;
    
    function __construct() {
        // Looing for .env at the root directory
        $dotenv = Dotenv::createMutable(__DIR__);
        $dotenv->load();

        $this->slug = $_REQUEST["article_slug"];
        $this->apiConf = [
            'path' => $_ENV['ONE_PATH'],
            'token' => $_ENV['ONE_TOKEN']
        ];
    }
    
    function make_request() 
    {
        if(!$this->slug){
            echo json_encode(['sts' => 'error', 'message'=>'Bad Request']);
        }else{

            $body = $this->get_potret_article_id_by_slug();
            //echo $body;

            $body = json_decode($body, true);
            $body = $body['data'];
            print_r($body);

            if(count($body) <= 0){
                echo json_encode(['sts' => 'error', 'message'=>'Not found']);
            }else{
                $chunker = new maverickChunker($body);

                //populate all content into a single text
                $content = $body['body'];
                foreach($body['pages'] as $paging){
                    $content .= "<h2>".$paging['page_title']."</h2>";
                    $content .= html_entity_decode($paging['page_body']);
                }
                //return (json_decode($chunker->explode_content()));
                //echo $content;
                return ($chunker->parseNews(['content' => $content, 'origin' => 'merdeka.com']));
                
            }
        }
    }

    function get_potret_article_id_by_slug()
    {
        $client = new Client();

        $url = $this->apiConf['path'].'article/slug/'.$this->slug;

        $response = $client->request(
            'GET', 
            $url, 
            ['headers' => 
                [
                    'Authorization' => "Bearer ".$this->apiConf['token']
                ]
            ]
        );
        
        return $response->getBody();

    }

    function get_raw_source() 
    {
        $client = new Client();

        $url = $this->apiConf['path'].'article/'.$this->articleId.'/?filter[publisher_id]=4&lip6id=1&filter[type_id]=1&limit=20&apps=0&page=1';

        $response = $client->request(
            'GET', 
            $url, 
            ['headers' => 
                [
                    'Authorization' => "Bearer ".$this->apiConf['token']
                ]
            ]
        );
        
        return $response->getBody();

    }
}

header('Content-Type: application/json; charset=utf-8');
$exec = new chunkHandler();
print_r($exec->make_request());

