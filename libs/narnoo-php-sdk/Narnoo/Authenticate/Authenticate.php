<?php

namespace Narnoo\Authenticate;
/**
*
*/
class Authenticate extends \Narnoo\Base
{
    /**************************************************
    *
    *                --- TOKEN ---
    *
    **************************************************/
    
    /**
    *   @title: Get Products
    *   @date: 25.06.2018
    *   @param: int business ID [optional]
    *   @result: JSON
    */
    public function getToken($access, $secret)
    {   
        $authn =  array(
            "API-KEY"           => $access,
            "API-SECRET-KEY"    => $secret
        );


        try{
            $url = "/authenticate/token";
            
            $response = $this->callNarnooAuthentication("get",$url,$authn);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

    
    /**************************************************
    *
    *                --- .TOKEN ---
    *
    **************************************************/
    /**************************************************
    *
    *                --- Valid Check ---
    *
    **************************************************/
    
    /**
    *   @title: Get Products
    *   @date: 25.06.2018
    *   @param: int business ID [optional]
    *   @result: JSON
    */
    public function valid()
    {   
       
        try{
            $url = "/token/validate";
            $response = $this->callNarnooAPI("get",$url);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

    
    /**************************************************
    *
    *                --- .Valid Check ---
    *
    **************************************************/


}

?>