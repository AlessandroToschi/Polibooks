<?php
require_once("common.php");

global $template;

$contenuto = nl2br($template->getFile("cond_util")); // senza .htm
echo $template->getSite(THEME_SINGLE, "Condizioni di Utilizzo", $contenuto);
?>
