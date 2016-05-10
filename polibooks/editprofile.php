<?php
require_once("common.php");
if(!checkPermissions(USER_AUTHED))
	redirect("index.php");

if(!isset($_POST['save']))
	form();
else
	save();

function form($error="")
{
	global $user, $template, $uni;
	
	$gender = $user->getGender();
	$settings = unpackSettings($user->getSettings());
	
	$form = $template->getFile("editprofile");
	$form = $template->setVariables($form, array(
		"GENDER_UNKNOWN" => ($gender=='u'?'selected':''),
		"GENDER_FEMALE" => ($gender=='f'?'selected':''),
		"GENDER_MALE" => ($gender=='m'?'selected':''),
		"NAME" => h_clean($user->getName()),
		"SURNAME" => h_clean($user->getSurname()),
		"EMAIL" => h_clean($user->getEmail()),
		"PHONE" => h_clean($user->getPhone()),
		"USE_PHONE" => ($settings['use_phone']==true?'checked':''),
		"USE_EMAIL" => ($settings['use_email']==true?'checked':''),
		"USE_FB"	=> ($settings['use_fb']==true?'checked':''),
		"CAMPUS_OPTIONS" => asCheckbox($uni->getCampusList(), "campus", $user->getCampusList()),
	));
    if(strlen($error) > 0)
        setErrorMessage($error);
	echo $template->getSite(THEME_SINGLE, "Modifica profilo", $form);
	die();
}

function save()
{
	global $user, $db;
	
	$name = gpc2db(trim(@$_POST['name']));
	$surname = gpc2db(trim(@$_POST['surname']));
	$gender = gpc2db(@$_POST['gender']);
	$campus = @$_POST['campus'];
	$email = gpc2db(trim(@$_POST['email']));
	$phone = gpc2db(trim(@$_POST['phone']));
	$use_phone = @$_POST['use_phone']=='true'?true:false;
	$use_email = @$_POST['use_email']=='true'?true:false;
	$use_fb = @$_POST['use_fb']=='true'?true:false;
	
	if(strlen($name)==0) form("Nome non valido");
	if(strlen($surname)==0) form("Cognome non valido");
	if($gender!='u' && $gender!='m' && $gender!='f') form("Sesso non valido");
	if(!is_array($campus) || count($campus)==0 || !areValidCampus($_POST['campus'])) form("Non hai scelto nessun campus");
	
	if(!$use_email && !$use_phone && !$use_fb) form("Nessun metodo di comunicazione scelto");
	if($use_email && !isValidEmail($_POST['email'])) form("Email non valida");
	if($use_phone && !isValidPhone(toPhone($_POST['phone'])))form("Telefono non valido");

	$id = $user->getId();
	
	$settings = buildSettings(USER_ROLE_NORMAL, $use_email, $use_phone, $use_fb);

	$db->query("UPDATE users SET name='$name', surname='$surname', gender='$gender', email='$email', phone='$phone', settings=$settings WHERE id=$id");
	$db->query("DELETE FROM users_campus WHERE user_id=$id");
	
	if(!is_array($campus))
		$campus = array((int)$campus);
	foreach($campus as $c)
	{
		$c = (int) $c;
		$db->query("INSERT INTO users_campus VALUES(0,$id,$c)");
	}
	$row = $db->fetch($db->query("SELECT * FROM users WHERE id=$id"));
	$user->attach($row);
	setMessage("Dati salvati");
	redirect("index.php");
}
?>
