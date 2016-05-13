<?php
if(!defined("BASE_PATH"))
	define("BASE_PATH", ".");

date_default_timezone_set('Europe/Rome');

require_once(BASE_PATH."/includes/defines.php");
require_once(BASE_PATH."/includes/functions.php");

require_once(BASE_PATH."/includes/database.class.php");
require_once(BASE_PATH."/includes/user.class.php");
require_once(BASE_PATH."/includes/facebook.class.php");
require_once(BASE_PATH."/includes/template.class.php");
require_once(BASE_PATH."/includes/language.class.php");
require_once(BASE_PATH."/includes/university.class.php");
require_once(BASE_PATH."/config.php");

global $config;
session_set_cookie_params(86400);
@session_start();

$exts = array("mysql", "json", "openssl");
foreach($exts as $ext)
	if(!extension_loaded($ext))
		die("Missing extension: $ext.");

if(isset($_GET['k_x']))
{
	$kx = $_GET['k_x'];
	$kuser="";
	for($i=0;$i<strlen($kx);$i++)
		if(ctype_digit($kx[$i]))
			$kuser.=$kx[$i];
		else
			break;
	$kuser = (int)$kuser;
	if($kuser>0)
	{
		setcookie('k_x',substr($kx,strlen($kuser)));
		setcookie('k_u',$kuser);
	}
}

$language = new Language("it");
$db = new Database();
$db->connect($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

$template	= new Template();
$fb 		= new Facebook();
$uni		= new University(1);
$user		= new User();

if($user->isAuthed())
	$fb->setAccessToken($user->getRawData('access_token'));
?>
