<?php

namespace Narnoo\Media;
/**
*
*/
class Media extends \Narnoo\Base
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
    public function getImages($value)
    {   

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/image/list?".$query;
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
    public function getImageDetails($id,$value)
    {
        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/image/details/".$id."?".$query;
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
    public function getPrints($value)
    {   

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/brochure/list?".$query;
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
    public function getPrintDetails($id,$value)
    {
        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/brochure/details/".$id."?".$query;
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
    public function getVideos($value)
    {   

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/video/list?".$query;
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
    public function getVideoDetails($id,$value)
    {
        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/video/details/".$id."?".$query;
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
    public function getLogos($value)
    {   

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/logo/list?".$query;
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
    public function getLogoDetails($id,$value)
    {
        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/logo/details/".$id."?".$query;
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
    public function getAlbums($value)
    {   

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/album/list?".$query;
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
    public function getAlbumImages($id,$value)
    {
        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/album/images/".$id."?".$query;
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