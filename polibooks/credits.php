<?php
	require_once("common.php");
	if(!checkPermissions(USER_AUTHED))
		redirect("index.php");

	global $template, $user;

	$main = $template->getFile("credits");
	$main = $template->setVariables($main, array(
			"LINK" => $user->getShareLink(),
			"CREDITS" => $user->getCredits(),
		));

	echo $template->getSite(THEME_SINGLE, "Condividi Polibooks", $main);	
?>
