<?php

require_once("common.php");

global $fb, $template, $user;

if(isset($_GET['signout']))
{
	logout();
	setMessage("Logout effettuato");
	redirect("index.php");
}

if(isset($_SESSION['userData']) && !isset($_SESSION['reg_step']))
	redirect("index.php");

if($fb->checkState(@$_GET['state']))
{
	if(isset($_GET['error']))
		dieError("Si è verificato un errore");
	else if(isset($_GET['state']) && isset($_GET['code']))
	{
		$state = @$_GET['state'];
		$code = @$_GET['code'];
		
		$ret = $fb->processAuth($state, $code);
		if($ret)
		{
			$fbUserData = @$_SESSION['fb_userdata'];
			$ss = explode(" ", $fbUserData['name']);
			$userInfo = User::getUserInfo($fbUserData['id'], $ss[0], $ss[1]);
			if($userInfo === false)
			{
				$_SESSION['reg_step'] = 1;
				redirect("?");
			}
			else
			{
				unset($_SESSION['reg_step']);
				$_SESSION['userData'] = $userInfo;
				$user->attach($userInfo);
				finalRedirect();
			}
		}
		else
		{
			setErrorMessage("Problema con facebook");
			redirect("index.php");
		}
	}

}

switch(@$_SESSION['reg_step'])
{
	case 1:
		$fbUserData = $_SESSION['fb_userdata'];
		
		// safety check
		if(isset($fbUserData['id']) && strlen(@$_SESSION['fb_token'])>0)
		{
			if(!isset($_POST['data']))
				showForm($fbUserData);
			else
			{
				$dataCorrect = checkData($fbUserData);
				if($dataCorrect === true)
				{
					$userData = storeData($fbUserData);
					$_SESSION['userData'] = $userData;
					$user->attach($userData);
					unset($_SESSION['reg_step']);
					finalRedirect();
				}
				else
					showForm($fbUserData, $dataCorrect);
			}
			break;
		}
		else
		{
			logout();
			setErrorMessage("Errore di autenticazione, riprova...");
			redirect("index.php");
		}	
	default: /* pagina aperta, login a fb */
		$url = $fb->getAuthUrl();
		redirect($url);
		break;
}

function checkData($userData)
{
	if(strlen($userData['first_name'])==0 || strlen($userData['last_name'])==0)
		return "Account di facebook non valido";
	
	$use_email 	= (@$_POST['use_email']=='true'?true:false);
	$use_phone 	= (@$_POST['use_phone']=='true'?true:false);
	$use_fb		= (@$_POST['use_fb']=='true'?true:false);
	
	if(!$use_email && !$use_phone && !$use_fb)
		return "Nessun metodo di comunicazione scelto";
	
	if($use_email && !isValidEmail($_POST['email']))
		return "Email non valida";
		
	if($use_phone && !isValidPhone(toPhone($_POST['phone'])))
		return "Telefono non valido";
		
	if(!is_array($_POST['campus']) || count($_POST['campus']) == 0 || !areValidCampus($_POST['campus']))
		return "Campus non validi";

	if(@$_POST['contract']!="true")
		return "Devi accettare le condizioni di utilizzo per poter continuare";

	return true;
}

function storeData($userData)
{
	return addNewUser(	$userData['id'], $userData['first_name'], 
						$userData['last_name'], @$userData['gender'], 
						trim($_POST['email']), @$_POST['use_email'], 
						@$_POST['use_phone'], @$_POST['phone'], 
						@$_POST['use_fb'], $_POST['campus'], $_SESSION['fb_token']
					 );
}

function showForm($userData, $error="")
{
	global $template, $uni;

	$form = $template->getFile("register_form");

	$v = array(
		"NAME"	=> $userData['first_name'],
		"FB_EMAIL" => $userData['email'],
		"CAMPUS_OPTIONS" => asCheckbox($uni->getCampusList(), "campus"),
		"ERROR" => $error,
		"ACTION" => "?"
	); 
	
	$form = $template->setVariables($form, $v);
	print_r(@$_SESSION['fb_userdata']);
	echo $template->getSite(THEME_SINGLE, "Registrati", $form);
}

function logout()
{
	unset($_SESSION['reg_step']);
	unset($_SESSION['fb_token']);
	unset($_SESSION['user_data']);
	@session_destroy();
	@session_start();
}

function finalRedirect()
{
	$to = @$_SESSION['redirectAfterLogin'];
	if(strlen($to)>0)
		redirect($to);
	else
		redirect("index.php");
}
?>
