<?php

namespace Narnoo\Connect;
/**
*
*/
class Connect extends \Narnoo\Base
{
    
    public function getFollowing($value)
    {
        try{
            $url = "/connect/following";
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    /*
    *
    *   {"latitude":"167";"longitude":"-16"}
    */
    public function findBusinesses($value)
    {
        try{
            $url = "/connect/find";
            $response = $this->callNarnooAPI("post",$url,NULL, $value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

    /*
    *
    *   {"name":"Narnoo"}
    */
    public function searchBusinesses($value)
    {
        try{
            $url = "/connect/search";
            $response = $this->callNarnooAPI("post",$url,NULL,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

    /*
    *
    *   {"type":"operator","id":78}
    */
    public function followBusinesses($value)
    {
        try{
            $url = "/connect/add";
            $response = $this->callNarnooAPI("post",$url,NULL,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }
    /*
    *
    *   {"type":"operator","id":78}
    */
    public function removeBusinesses($value)
    {
        try{
            $url = "/connect/remove";
            $response = $this->callNarnooAPI("post",$url,NULL,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }






}

?>