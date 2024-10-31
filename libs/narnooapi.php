<?php 

include "narnoo-php-sdk/vendor/autoload.php";


use Narnoo\Connect\Connect;
use Narnoo\Business\Business;
use Narnoo\Product\Product;
use Narnoo\Booking\Booking;



class Narnoosdk
{

	protected $token;

	public function __construct($token){
		$this->token = $token;
	}


	public function following($page=1){

		if(!empty($page)){
			$value = array(
				'page' => $page
			);
		}
		
		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->getFollowing($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}


	public function find($page=1){

		if(!empty($page)){
			$value = array(
				'page' => $page
			);
		}
		
		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->findBusinesses($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}


	public function search($search){

		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->searchBusinesses($search);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function followBusiness($connect){

		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->followBusinesses($connect);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function followOperator($connect){

		$value = array("type"=>"operator","id" =>$connect );

		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->followBusinesses($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function removeOperator($connect){

		$value = array("type"=>"operator","id" =>$connect );

		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->removeBusinesses($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}


	public function getBusinessListing( $id ){
		
		$listing = new business();
		$listing->setToken($this->token);
		$list 	 = $listing->getListing( $id );
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function getProducts( $operator = NULL ){
    	$product = new product();
		$product->setToken($this->token);
		$details 	 = $product->getProducts($operator);
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function getBookableProducts( $operator = NULL ) {
		$booking = new booking();
		$booking->setToken($this->token);
		$details 	 = $booking->getBookableProducts($operator);
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function getProductDetails($id, $operator = NULL){
		
		$product = new product();
		$product->setToken($this->token);
		$details 	 = $product->getProductDetails( $id, $operator );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	
	}


}
