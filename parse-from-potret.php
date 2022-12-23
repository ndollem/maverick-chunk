<?php

require_once "vendor/autoload.php";
//require_once "src/maverickChunker-php5.php";
require_once "src/maverickChunker.php";

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
            //echo $body;

            $body = json_decode($body, true);
            print_r($body);

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

        $url = 'https://www.one.co.id/api/article/3220228/?filter[publisher_id]=4&lip6id=1&filter[type_id]=1&limit=20&apps=0&page=1';

        $response = $client->request(
            'GET', 
            $url, 
            ['headers' => 
                [
                    'Authorization' => "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjljOTEwMTRjMTRmYzgwODJjZWEwYWU1MjZjMjBmN2I5OWYyMzdhMWJkYWQ4NDcwMGFlNDU1NTk0ZTMwNWFkNDE5YzQ0Yzk2NWJhN2JhYjgzIn0.eyJhdWQiOiIyMiIsImp0aSI6IjljOTEwMTRjMTRmYzgwODJjZWEwYWU1MjZjMjBmN2I5OWYyMzdhMWJkYWQ4NDcwMGFlNDU1NTk0ZTMwNWFkNDE5YzQ0Yzk2NWJhN2JhYjgzIiwiaWF0IjoxNjcxNDk1MTM3LCJuYmYiOjE2NzE0OTUxMzcsImV4cCI6MTcwMzAzMTEzNywic3ViIjoiIiwic2NvcGVzIjpbXX0.L7BqvHgn94mXzhv-OTngyE4eyhDLAkVDEOvzx_hRAAE-hYxVCTatnGnO3GW95Z48Hj38W1MW2zTZ-FvT1fX4wruImXH-G8PvGx-NoAtrFP1FPFt22CmoCoqkZLv--rAKZt4WxhDqtSMwp2BG3euwkwsLX_Qy8IZOzOh6xfk0iFO5j-JaWnYXNb4TP-oWioTS3RYPGrgQ-857nCgH9dfouNX4Uj8PmwBY74tJPvpekg4xQPXACfnQ5wg0Zqe6_EgAbwSxd-VvbgNhvHKQybklWW-emM7_Y_g5nCUGEebXLVJqLd8j-TXHa1qb6rn3BIFAxiZ2NU17DguDYAnrj72x4IGtX97ZHrYTuMNoKBSsiRhzw6zgI6k9J9L5UXjo_xVJdxE6zD6HqPptiMX4FP7W2AuEzIxr0Uxpar7CTmiXPj3gaZ_bAT704Zbu86DE1mtRhLq4NwjD4dCmkZoO4yLLSzLZa_7xEm7GTqV8G6rd_eU6jtFk7WAwBcsTso8ErnSobZgWrlA1dHFgZI4GNKHbCn7ohBOBxEpnKZt4N0BlYzGkmYw23URKHI4o9RGdCXE47Z4WPoijJw2cIHk195eFPmk2c2x6bCqu1oTK4sVSeEV18PElgogTIV0xhIgLqj6KuMgyD3ZX4897gZSM9FyKQgWq4ppjAkfwRV6MODEVzmU"
                ]
            ]
        );
        
        return $response->getBody();

    }
}

header('Content-Type: application/json; charset=utf-8');
$exec = new chunkHandler();
print_r($exec->make_request());

