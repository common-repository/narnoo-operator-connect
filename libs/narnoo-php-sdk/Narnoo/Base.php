<?php 

namespace Narnoo;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

/**
*
*/
class Base
{

    const API_URL = "https://apis.narnoo.com/api/v1/";

    protected $client;
    protected $token;
    protected $secretkey;
    protected $accesskey;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    public function setToken($token)
    {
        $this->token 		= $token;
    }

    protected function callNarnooAuthentication($method,$request,$authn)
    {
        try{
            $url    = self::API_URL . $request;
            $header = array('headers' => $authn);            

            $response = $this->client->request($method,$url,$header);
            $result = json_decode($response->getBody()->getContents());

            return $result->token;
        }
        catch (RequestException $e) {
            $response = $this->statusCodeHandling($e);
            return $response;
        }
    }
    protected function callNarnooAPI($method,$request,$post = [],$json = NULL )
    {
        try{

            //$this->prepareAccessToken();
            $url = self::API_URL . $request;
            $header = array('Authorization'=>'bearer '. $this->token);
            $data   = array('query' => $post,'headers' => $header);

            if(!empty($json)){
                $payload  = json_encode($json);
                
                $jsonHeader = array( 
                    'Content-Type'=>'application/json',
                );
                $header = array_merge($header,$jsonHeader);
                
                $jsonData = array( 
                    'body' => $payload,
                );
                $data = array_merge($data,$jsonData);
            }

            $response = $this->client->request($method,$url,$data);
            

            return json_decode($response->getBody()->getContents());

            //return $data;
        
        } catch (RequestException $e) {
            $response = $this->StatusCodeHandling($e);
            return $response;
        }
    }
    protected function statusCodeHandling($e)
    {
        $response = array("statuscode" => $e->getResponse()->getStatusCode(),
        "error" => json_decode($e->getResponse()->getBody(true)->getContents()));
        return $response;
    }
}