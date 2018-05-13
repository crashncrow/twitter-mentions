<?php
//Configuracion
date_default_timezone_set('America/Buenos_Aires');

//Conexion a la base de datos
define("SERVER", $_SERVER['DB_HOST']);
define("USER",   $_SERVER['DB_USER']);
define("PASS",   $_SERVER['DB_PASS']);
define("DBNAME", $_SERVER['DB_NAME']);
define("DEBUG", false);

require_once('tw/FollowMe.class.php');

/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$settings = array(
    'oauth_access_token' 		=> "",
    'oauth_access_token_secret' => "",
    'consumer_key' 				=> "",
    'consumer_secret' 			=> "",
    'account'					=> "",
    'debug'						=> true
);

define("SETTINGS", $settings);

if(DEBUG){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
