<?php
require_once("common.php");

global $template;

$contenuto = nl2br(utf8_decode($template->getFile("privacy"))); // senza .htm
echo $template->getSite(THEME_SINGLE, "Privacy", $contenuto);
?>
