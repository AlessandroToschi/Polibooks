<?php
require_once("common.php");
checkPermissions(USER_GUEST | USER_AUTHED);
global $template;

$main = getMainView();
echo $template->getSite(THEME_BASE,"", $main);

function getMainView()
{
	global $user;

	$out = <<<HTML
	<script>window.onload=function(){loadMain();}</script>
	<div id="divMaster">
		<div align="center" id="divMain">
		<div align="center" id="mainMessage">Benvenuto nella prima piattaforma libera e autonoma creata da studenti per favorire l'incontro, senza intermediari, fra venditori ed acquirenti di libri usati!<br />
HTML;
/*'*/

	if(!$user->isAuthed())
		$out.= 'Per vendere o metterti in contatto con il venditore di un libro ti basta effettuare il <a href="/polibooks/auth.php">login</a> con Facebook.<br />';
	
	$out.=<<<HTML
<br />Ricordati che Polibooks premia gli utenti che aiutano a pubblicizzarlo: una volta effettuato il login, puoi accedere alla pagina "I tuoi crediti e il Link", la quale ti fornisce un link da condividere con i tuoi amici. Ogni amico iscritto tramite quel link ti far&agrave; guadagnare <b>5</b> crediti. Pi&ugrave; crediti hai, pi&ugrave; i tuoi libri appariranno in alto nella ricerca!<br /><br />Ricordati di segnare il libro come &laquo;Venduto&raquo; (da "Gestisci i libri") una volta che lo scambio è stato effettuato!<br /><br /></div>

        <div class="panel panel-primary">
            <div class="panel-heading">Ultimi libri aggiunti</div>
            <div class="panel-body" id="divBooks"></div>
        </div>
	
		<div align="center" id="divSearch" style="display: hidden;"></div>
		<div id="loadingAnim" style="width: 100%; text-align: center; display: none;"><img src="/polibooks/img/loading2.gif" /></div>
		<div id="showbook"></div>
	</div>
HTML;
	return $out;
	
}
echo @$_SESSION['fb_userdata'];
?>
