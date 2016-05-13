<?php
require_once("common.php");
	
$id = (int)@$_GET['id'];
if($id<=0)
	dieError("Id non valido");

global $db, $template,$books_quality, $user;

$is_authed = $user->isAuthed();

$q = $db->query("SELECT title,authors,isbn10,isbn13,books_user.publish_date,year,price,quality,users.name,surname,fb_id,thumbnail,email,phone,settings,fb_id, books_user.status,notes,GROUP_CONCAT(campus.name ORDER BY campus.name ASC) as `cnames` ".
				"FROM books,books_user,users,campus,users_campus ".
				"WHERE books_user.book_id = books.id AND books_user.user_id = users.id AND users_campus.user_id = users.id AND campus.id=users_campus.campus_id AND books_user.id=$id AND books_user.status IN(".BPU_STATUS_PUBLISHED.",".BPU_STATUS_SOLD.") GROUP BY books_user.id");
if($db->rows($q)!=1)
	die("Id non valido");
$r = $db->fetch($q);
if($r['status'] == BPU_STATUS_SOLD)
	die(utf8_encode('<div align="center">Il libro richiesto è stato venduto e non è più disponibile.</div>'));

$settings = unpackSettings($r['settings']);

$emailbutton=$phonebutton=$fbbutton=$pleaselogin=$pubdate=$pubuser="";
if($is_authed)
{
	if($settings['use_email'])
		$emailbutton = '<tr><td>Email</td><td><a href="mailto:'.h_clean($r['email']).'">'.h_clean($r['email']).'</a></td></tr>';
	if($settings['use_phone'])
		$phonebutton = '<tr><td>Telefono</td><td>'.h_clean($r['phone'])."</td></tr>";
	if($settings['use_fb'])
		$fbbutton = '<tr><td>Profilo di FB</td><td><a href="https://www.facebook.com/'.h_clean($r['fb_id']).'">'.h_clean($r['name'])." ".h_clean($r['surname']).'</a></td></tr>';
	$pubuser = "<tr><td>Pubblicato da</td><td>".h_clean($r['name'])." ".h_clean($r['surname'])."</td></tr>";
	$pubdate = "<tr><td>Pubblicato il</td><td>".date("d/m/Y H:i", $r['publish_date'])."</td></tr>";

	$notes = trim($r['notes']);
	$notes = wordwrap($notes, 43, "\n", true);
	$notes = h_clean($notes);
	if(strlen($notes)>0)
	{
		$outnotes = '<tr><td colspan="2" class="sbnot">Note</td></tr><tr><td style="text-align: center;">'.$notes.'<br /><br /></td></tr>';
	}
	else
		$outnotes = '';
}
else
{
		$pleaselogin = '<tr><td>Effettua il <a href="mustlogin.php?to=book&id='.$id.'">login</a> per poter contattare il venditore</td></tr>';
		$outnotes='';
}

if(strlen($r['thumbnail'])==0)
	$r['thumbnail']="/img/noimg.png";
$form = $template->getFile("showbook");
$form = $template->setVariables($form, array(
	"TITLE" => h_clean($r['title']),
	"AUTHOR" => h_clean($r['authors']),
	"ISBN10" => h_clean($r['isbn10']),
	"ISBN13" => h_clean($r['isbn13']),
	"YEAR" => (int) $r['year'],
	"NOTES" => $outnotes,
	"PRICE" => sprintf("&euro; %.2f",((int)$r['price'])/100),
	"QUALITY" => $books_quality[$r['quality']], 
	"PUBDATE" => $pubdate,
	"PUBUSER" => $pubuser,
	"WHERE" => h_clean($r['cnames']),
	"IMAGE" => $r['thumbnail'],
	"EMAIL_BUTTON" => $emailbutton,
	"PHONE_BUTTON" => $phonebutton,
	"FB_BUTTON" => $fbbutton,
	"LOGIN_PLEASE" => $pleaselogin
));
echo utf8_encode($form);
die();

?>
