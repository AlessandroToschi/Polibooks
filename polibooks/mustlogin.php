<?php
require_once("common.php");

$file = "";
switch(@$_GET['to'])
{
	case "sell": $file="sell.php"; break;
	case "book": $file="index.php#mode=show&id=".(int)$_GET['id']; break;
	default: $file="index.php"; break;
}

$_SESSION['redirectAfterLogin'] = $file;

if(@$_GET['to'] == "book")
	redirect("auth.php");
global $template;

$main = $template->getFile("mustlogin");
echo $template->getSite(THEME_SINGLE,"Ehi, aspetta!", $main);
?>
