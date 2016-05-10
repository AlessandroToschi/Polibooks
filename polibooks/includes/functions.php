<?php
require_once(BASE_PATH."/includes/defines.php");

function checkPermissions($pageLevel)
{
	global $user;
	$pageGuest = $pageLevel & USER_GUEST;
	$pageAuthed = $pageLevel & USER_AUTHED;
	
	$curLevel = @$_SESSION['level'];

	if(@$_SESSION['reg_step'] == 1)
	{
		redirect("auth.php");
		die();
	}
	
	if($user->isAuthed() && !$pageAuthed)
		return false;
	
	if(!$user->isAuthed() && !$pageGuest)
		return false;
		
	return true;
}

function genRand($len)
{
	$str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	return substr(str_shuffle($str),0,$len);
}

function getCopyrightBar()
{
	return <<< __HTML__
	<table id="copytable"><tr>
	<td><a href="/cond_util.php">Condizioni di utilizzo</a></td>
	<td>Copyright &copy; 2013-2014 Polibooks Team</td>
	<td><a href="/policy.php">Privacy</a></td>
	</tr></table>
__HTML__;
}

function antiCSRF($name)
{
	if(@$_POST['__key'] == @$_SESSION["k_$name"] && strlen($_POST['__key'])>0)
		return true;
	return false;
}

function request($name, $msg, $vars)
{
	global $template;
	$req = $template->getFile("request");

	$htmlvars="";

	$csrf = md5(microtime());
	$vars["__name"] = $name;
	$vars["__key"] = $csrf;
	$_SESSION["k_$name"] = $csrf;

	foreach($vars as $key=>$val)
	{
		$key = urlencode($key);
		$val = urlencode($val);
		$htmlvars.= "<input type=\"hidden\" name=\"$key\" value=\"$val\" />";
	}

	$req = $template->setVariables($req, array(
		"MESSAGE" => nl2br($msg),
		"VARS"	=> $htmlvars));

	die($template->getSite(THEME_SINGLE, "Conferma", $req));
}

function draw_header($title)
{
	global $template;
	
	$variables = array(
		"page_title" => $title,
		"menu" => menubar(),
		"messages" => messagebar()
	);
	
	echo $template->getPage("header", $variables);
}

function setErrorMessage($str)
{
	$_SESSION['errorMessage'] = $str;
}

function setMessage($str)
{
	$_SESSION['showMessage'] = $str;
}

function draw_bottom()
{
	?>
	</div>
	<div class="copyright"><?php echo copyright(); ?></div><br />
	</body>
	</html>
	<?php
}

function do_get_request($url)
{	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

	$f = curl_exec($ch);

	curl_close($ch);

	return $f;

}

function do_post_request($url, $data)
{
	$pdata="";
	foreach($data as $key=>$value) { $pdata .= urlencode($key).'='.urlencode($value).'&'; }
	rtrim($pdata, '&');

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, count($data));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $pdata);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

	$f = curl_exec($ch);

	curl_close($ch);

	return $f;
}

function redirect($page)
{
	die(header("Location: $page"));
}

function check_referer()
{
	return (@eregi(SITE,$_SERVER['HTTP_REFERER']));
}

function split_url($url)
{
	$out = array();
	$a = parse_url ($url);
	
	$out[0] = $a['path'];
	
	$args = explode("&", $a['query']);
	
	$out[1] = array();
	foreach ($args as $arg)
	{
		$x = explode("=", $arg);
		$out[1][@$x[0]] = @$x[1];
	}
	
	return $out;
}

function gpc2db($string)
{
	$string = trim($string);
	if(!get_magic_quotes_gpc())
		$string = mysql_real_escape_string($string);
	return $string;
}

function h_clean($string)
{
    return htmlentities($string,ENT_COMPAT|ENT_HTML401|ENT_SUBSTITUTE,"UTF-8");
}

function dieError($error)
{
	global $template;
	die($template->getSite("Error", "$error", ""));
}

function asOptions($array, $message="")
{
	if(strlen($message)>0)
		$ret = '<option value="" disabled selected style="display:none;">'.$message.'</option>';
	else
		$ret = "";
	foreach($array as $key=>$value)
		$ret .= '<option value="'.h_clean($key).'">'.h_clean($value).'</option>';
	return $ret;
}

function asCheckbox($array, $name, $checked_list=array())
{
	$ret = '';
	foreach($array as $key=>$value)
		$ret .= '<div class="checkbox"><label><input type="checkbox" name="'.h_clean($name).'[]" value="'.h_clean($key).'" '.(in_array($key, $checked_list)?'checked':'').'>'.h_clean($value).'</label></div>';
	return $ret;
}


function toPhone($num)
{
	return preg_replace("/[^0-9\\+]/", '', $num);
}

function isValidPhone($num)
{
	return @eregi("^(\\+)?[0-9]{7,14}$", $num);
}

function isValidEmail($email)
{
	return @eregi("^[a-zA-Z0-9_\\.\\+\\-]+@[a-zA-Z0-9\\-]+\\.[a-zA-Z0-9\\-\\.]+$", $email);
}

function areValidCampus($campus)
{
	global $uni;
	
	$realCampus = $uni->getCampusIds();
	
	foreach($campus as $myCampus)
		if(!in_array($myCampus, $realCampus))
			return false;
	return true;
}

function buildSettings($role, $use_email, $use_phone, $use_fb)
{
	$bits = $role;

	$bits |= ($use_email & 0x01) * USER_SHOWMAIL;
	$bits |= ($use_phone & 0x01) * USER_SHOWPHONE;
	$bits |= ($use_fb & 0x01) * USER_SHOWFB;
	return $bits;
}

function unpackSettings($map)
{
	$out = array();
	
	$out['role'] = $map & USER_ROLE_MASK;
	$out['use_email'] = ($map & USER_SHOWMAIL)!=0?true:false;
	$out['use_phone'] = ($map & USER_SHOWPHONE)!=0?true:false;
	$out['use_fb'] = ($map & USER_SHOWFB)!=0?true:false;
	return $out;
}

function cleanISBN($isbn)
{
	$tmp = "";
	for($i=0;$i<strlen($isbn);$i++)
	{
		$c = $isbn[$i];
		if($c>='0' && $c<='9')
			$tmp .= $c;
	}
	return $tmp;	
}

function getBook($isbn)
{
	global $db;

	$isbn = cleanISBN($isbn);

	$q = $db->query("SELECT * FROM books WHERE isbn10='$isbn' OR isbn13='$isbn'");
	if($db->rows($q))
	{
		$r = $db->fetch($q);
		return $r['id'];
	}
	else
	{
	$f = file_get_contents("https://www.googleapis.com/books/v1/volumes?q=isbn:".urlencode($isbn));
	$json = json_decode($f, true);

	$count = $json['totalItems'];
	if($count == 0)
		return false;
	else if($count == 1)
	{
		$book = $json['items'][0];
		
		$v = $book['volumeInfo'];
		$title = $v['title'];
		$authors = $v['authors'];
		$pageCount = $v['pageCount'];
		$publishedDate = $v['publishedDate'];
		$thumbnail = @$v['imageLinks']['thumbnail'];
		
		$isbn10 = (strlen($isbn)==10?$isbn:"");
		$isbn13 = (strlen($isbn)==13?$isbn:"");
		
		$idcat = $v['industryIdentifiers'];
		foreach($idcat as $ids)
		{
			if($ids['type']=="ISBN_10")
				$isbn10 = $ids['identifier'];
				
			if($ids['type']=="ISBN_13")
				$isbn13 = $ids['identifier'];
		}
		
		$id = $db->storeBook($isbn10, $isbn13, $title, $publishedDate, $pageCount, $authors, $thumbnail);
		return $id;
	}
	}
}

/*
chiamata da auth.php
*/
function addNewUser($fb_id, $name, $surname, $gender, $email, $use_email, $use_phone, $phone, $use_fb, $campus, $token)
{
	global $uni, $db, $fb;
	
	$fb_id = $db->escape($fb_id);
	$name = $db->escape(utf8_decode($name));
	$surname = $db->escape(utf8_decode($surname));
	$gender = $db->escape($gender);
	$email = $db->escape($email);
	$phone = $db->escape($phone);
	$token = $db->escape($token);

	$use_email 	= ($use_email=='true'?1:0);
	$use_phone 	= ($use_phone=='true'?1:0);
	$use_fb		= ($use_fb=='true'?1:0);

	$q = $db->query("SELECT * FROM users WHERE fb_id = '$fb_id'");
	if($db->rows($q)!=0)
	{
		$row = $db->fetch($q);
		$id = $row['id'];
		$db->query("UPDATE users SET access_token='$token' WHERE id=$id");
		$row['access_token'] = $token;
		
		return $row;
	}

	if(strlen($gender)==0)
		$gender='?';
	else
		$gender = substr($gender, 0, 1);

	$settings = buildSettings(USER_ROLE_NORMAL, $use_email, $use_phone, $use_fb);
	
	$ip = $_SERVER['REMOTE_ADDR'];

	$randcode = genRand(5);

	$parentuid=0;
	$basecred=10;
	if(isset($_COOKIE['k_x']) && isset($_COOKIE['k_u']))
	{
		$k_id = (int) $_COOKIE['k_u'];
		$k_code = $db->escape($_COOKIE['k_x']);
		$qx = $db->query("SELECT COUNT(*) FROM users WHERE id=$k_id AND authCode='$k_code'");
		$qr = $db->fetch($qx);
		if($qr[0]==1)
		{
			$parentuid=$k_id;
			$db->query("UPDATE users SET credits=credits+5 WHERE id=$k_id");
			$basecred=15;
		}
		setcookie("k_x", "", time()-3600);
		setcookie("k_u", "", time()-3600);
	}

	$db->query("INSERT INTO users VALUES(0, '$name', '$surname', '$gender', '$email', '$phone', '$randcode', $parentuid, $basecred, ".time().", ".time().", '$ip', 'it', $settings, '$fb_id', '$token')");
	$id = mysql_insert_id();
	
	if(!is_array($campus))
		$campus = array((int)$campus);
	foreach($campus as $c)
	{
		$c = (int) $c;
		$db->query("INSERT INTO users_campus VALUES(0,$id,$c)");
	}
	
	return $db->fetch($db->query("SELECT * FROM users WHERE id=$id"));
}
?>
