<?php 

include "narnoo-php-sdk/vendor/autoload.php";

use Narnoo\Authenticate\Authenticate;

class NarnooToken
{

	public function authenticate($access, $secret){

		$authn = new Authenticate();
		$token = $authn->getToken($access,$secret);
		if(!empty($token) && is_string($token)){
			return $token;
		}else{
			return NULL;
		}
	}

	public function validCheck($token){

		$authn = new Authenticate();
		$authn->setToken($token);
		$token = $authn->valid();
		if( !empty($token->data) ){
			return TRUE;
		}else{
			return FALSE;
		}
	}


}
