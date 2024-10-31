<?php

namespace Narnoo\Access;
/**
*
*/
class Access extends \Narnoo\Base
{
    /**************************************************
    *
    *                --- IMAGES ---
    *
    **************************************************/
    
    /**
    *   @title: Get Images
    *   @date: 25.06.2018
    *   @param: array page | total
    *   @result: JSON
    */
    public function getBusinessImages( $businessType, $businessId, $value )
    {   

        $type = lcfirst($businessType);

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/image/list/".$type."/".$businessId."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


     /**
    *   @title: Get Image Details
    *   @date: 25.06.2018
    *   @param: int image ID
    *   @param: array page | total
    *   @result: JSON
    */
    public function getBusinessImageDetails( $id, $businessType, $businessId, $value )
    {

        $type = lcfirst($businessType);

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/image/details/".$id."/".$type."/".$businessId."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    /**************************************************
    *
    *                --- .IMAGES ---
    *
    **************************************************/

    /**************************************************
    *
    *                --- BROCHURES ---
    *
    **************************************************/
    
    /**
    *   @title: Get Images
    *   @date: 25.06.2018
    *   @param: array page | total
    *   @result: JSON
    */
    public function getBusinessPrints($businessType, $businessId, $value)
    {   
        $type = lcfirst($businessType);

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/brochure/list/".$type."/".$businessId."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


     /**
    *   @title: Get Image Details
    *   @date: 25.06.2018
    *   @param: int image ID
    *   @param: array page | total
    *   @result: JSON
    */
    public function getBusinessPrintDetails( $id, $businessType, $businessId, $value )
    {
        $type = lcfirst($businessType);

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/brochure/details/".$id."/".$type."/".$businessId."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    /**************************************************
    *
    *                --- .BROCHURES ---
    *
    **************************************************/
    /**************************************************
    *
    *                --- Videos ---
    *
    **************************************************/
    
    /**
    *   @title: Get Images
    *   @date: 25.06.2018
    *   @param: array page | total
    *   @result: JSON
    */
    public function getBusinessVideos($businessType, $businessId, $value)
    {   

        $type = lcfirst($businessType);

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/video/list/".$type."/".$businessId."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


     /**
    *   @title: Get Image Details
    *   @date: 25.06.2018
    *   @param: int image ID
    *   @param: array page | total
    *   @result: JSON
    */
    public function getBusinessVideoDetails( $id, $businessType, $businessId, $value )
    {

        $type = lcfirst($businessType);

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/video/details/".$id."/".$type."/".$businessId."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    /**************************************************
    *
    *                --- .Videos ---
    *
    **************************************************/

    /**************************************************
    *
    *                --- Logos ---
    *
    **************************************************/
    
    /**
    *   @title: Get Images
    *   @date: 25.06.2018
    *   @param: array page | total
    *   @result: JSON
    */
    public function getBusinessLogos($businessType, $businessId, $value)
    {   
        $type = lcfirst($businessType);

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/logo/list/".$type."/".$businessId."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


     /**
    *   @title: Get Image Details
    *   @date: 25.06.2018
    *   @param: int image ID
    *   @param: array page | total
    *   @result: JSON
    */
    public function getBusinessLogoDetails( $id, $businessType, $businessId, $value )
    {
        $type = lcfirst($businessType);

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/logo/details/".$id."/".$type."/".$businessId."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    /**************************************************
    *
    *                --- .Logos ---
    *
    **************************************************/

    /**************************************************
    *
    *                --- Albums ---
    *
    **************************************************/
    
    /**
    *   @title: Get Images
    *   @date: 25.06.2018
    *   @param: array page | total
    *   @result: JSON
    */
    public function getBusinessAlbums($businessType, $businessId, $value)
    {   
        $type = lcfirst($businessType);

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/album/list/".$type."/".$businessId."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


     /**
    *   @title: Get Image Details
    *   @date: 25.06.2018
    *   @param: int image ID
    *   @param: array page | total
    *   @result: JSON
    */
    public function getBusinessAlbumImages( $id, $businessType, $businessId, $value )
    {
        $type = lcfirst($businessType);

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/album/images/".$id."/".$type."/".$businessId."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    /**************************************************
    *
    *                --- .Logos ---
    *
    **************************************************/



}

?>