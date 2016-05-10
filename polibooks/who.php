<?php
require_once("common.php");

global $template;

$contenuto = $template->getFile("who_are_we");
echo $template->getSite(THEME_SINGLE, "Chi Siamo", $contenuto);
?>
