<?php
require_once("common.php");

global $template;

$contenuto = $template->getFile("search"); // senza .htm
echo $template->getSite(THEME_SINGLE, "Cerca un libro", $contenuto);
?>
