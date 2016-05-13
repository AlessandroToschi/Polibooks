<?php

$db_server = '127.0.0.1';
$db_user = 'aleto';
$db_pass = 'toscolino';
$db_name = 'TestDB';

//$db_connection = mysqli_connect($db_server, $db_user, $db_password, $db_name);

$conn = mysqli_connect($db_server, $db_user, 'toscolino',$db_name);

if(!$conn)
{
	die("Connection failed: ".mysqli_connect_error());
}
echo "Connected successfully";

$s = "ciao(\'";
$s .= "pippo\'\'";

echo $s;

mysqli_close($conn);
?>