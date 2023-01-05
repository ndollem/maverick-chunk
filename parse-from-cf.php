<?php

require_once "vendor/autoload.php";
//require_once "src/maverickChunker-php5.php";
//require_once "src/maverickChunker-thin.php";
require_once "src/maverickChunker.php";

use GuzzleHttp\Client;
use Dotenv\Dotenv;

class chunkHandler
{

    private $apiConf;
    private $articleId;
    private $debug;

    function __construct()
    {
        // Looing for .env at the root directory
        $dotenv = Dotenv::createMutable(__DIR__);
        $dotenv->load();

        $this->articleId = $_REQUEST["article_id"];
        $this->debug = isset($_REQUEST["debug"]) ? $_REQUEST["debug"] : false;
        
        $this->apiConf = [
            'path' => $_ENV['API_PATH'],
            'token' => $_ENV['API_TOKEN'],
            //'cloudPath' => 'http://localhost:8080'
            'cloudPath' => 'https://asia-southeast2-kly-microservices-373603.cloudfunctions.net/Maverick-Chunker'
        ];
    }

    function make_request()
    {
        if (!$this->articleId) {
            echo json_encode(['sts' => 'error', 'message' => 'Bad Request']);
        } else {

            $body = $this->get_raw_source();
            //echo $body.'<hr>';

            $body = json_decode($body, true);
            //print_r($body);

            if (count($body) <= 0) {
                echo json_encode(['sts' => 'error', 'message' => 'Not found']);
            } else {
                //populate all content into a single text
                $content = $body['news_content'];
                foreach ($body['news_paging'] as $paging) {
                    $content .= html_entity_decode($paging['content']);
                }
                if($this->debug) echo $content;

                $chunker = $this->processData($content);
                return ($chunker);
            }
        }
    }

    function get_raw_source()
    {
        $client = new Client();

        $url = $this->apiConf['path'] . '/news/'
            . $this->articleId
            . '/&token='
            . $this->apiConf['token'];

        $response = $client->request('GET', $url);

        return $response->getBody();
    }

    function processData($raw)
    {
        $client = new Client();

        $url = $this->apiConf['cloudPath'];
        /*$response = $client->request('POST', $url, [
            'content' => $raw
        ]);*/
        $options = [
            //'debug' => TRUE,
            'form_params' => [
                "content" => $raw
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        ];
        $response = $client->post($url, $options);
        //echo $response->getStatusCode();
        //echo '<pre>' . print_r((string)$response->getBody(), true) . '</pre>';
        //echo '<pre>' . print_r($response->getBody()->getContents(), true) . '</pre>';
        
        return $response->getBody()->getContents();
    }
}

header('Content-Type: application/json; charset=utf-8');
$exec = new chunkHandler();
$res = json_decode($exec->make_request());
print_r($res);
