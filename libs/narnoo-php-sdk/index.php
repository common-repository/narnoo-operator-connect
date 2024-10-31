<?php
include "vendor/autoload.php";
use Narnoo\Authenticate\Authenticate;

$list 	= new Authenticate();
//$list->setToken("[token]");
$key 	= '1234578';
$secret = '12345678';
$lists 	= $list->getToken($key,$secret);
echo "<pre>";
print_r($lists);
echo "</pre>";
?>