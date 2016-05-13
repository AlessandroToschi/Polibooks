<?php
define("BASE_PATH", "..");
require_once("../common.php");
define("LIMIT", 15);
define("MAX_LENGTH_TITLE", 50);
define("MAX_LENGTH_AUTHOR", 30);
checkPermissions(USER_GUEST | USER_AUTHED);

if(@$_GET['data'] == "lastest")
	getLastest((int) @$_GET['page']);

if(isset($_GET['text']))
	search($_GET['text'],(int)@$_GET['page']);
die();

function convertDate($date)
{
	$d = time()-$date;
	if($d<=40) return "Meno di un minuto fa";
	if($d<60*60) return sprintf("%d minut%s fa", (int)($d/60), (((int)($d/60)==1)?'o':'i'));
	if($d<60*60*24) return sprintf("%d or%s fa", (int)($d/60/60), (((int)($d/60/60)==1)?'a':'e'));
	return date("d/m/Y", $date);
}

function getLastest($offset)
{
	global $db;
	if($offset >= 3)
		die();

    $query = "SELECT bfound.id, title, authors, bfound.publish_date, year, price, quality, GROUP_CONCAT(campus.name ORDER BY campus.name ASC) as `cnames` FROM (SELECT books_user.id, books_user.user_id, title, authors, books_user.publish_date, year, price, quality FROM (SELECT * FROM books_user WHERE books_user.status=".BPU_STATUS_PUBLISHED." ORDER BY publish_date DESC LIMIT ".LIMIT." OFFSET ".(LIMIT * $offset).") as `books_user` INNER JOIN books ON (books_user.book_id = books.id) ) as `bfound` INNER JOIN users ON (bfound.user_id = users.id) INNER JOIN users_campus ON (users_campus.user_id = users.id) INNER JOIN campus ON (campus.id = users_campus.campus_id) GROUP BY bfound.id ORDER BY publish_date DESC";

    $q = $db->query($query);
	$out = presentQuery($q);
	die(json_encode($out));
}

function search($text, $offset)
{
	global $db, $template,$books_quality;

	for($i=0;$i<strlen($text);$i++)
		if(!@eregi("[a-z0-9 \\._\\-]",$text[$i]))
			$text[$i]=" ";
	$text=trim(gpc2db($text));
	if(strlen($text)<3)
		die(json_encode(array()));
	
	$isbn = checkISBN($text);
	if($isbn !== false)
	{
		$len = strlen($isbn);
		$condition = "isbn$len='$isbn'";
	}
	else
	{
		$arr = txt2arr($text);
		$row = implode(" ",$arr);
		$condition = "MATCH(title, authors) AGAINST($row IN BOOLEAN MODE)";
	}

    $query= "SELECT bfound.id, title, authors, bfound.publish_date, year, price, quality, GROUP_CONCAT(campus.name ORDER BY campus.name ASC) as `cnames` FROM (SELECT  books_user.id, books_user.user_id, title, authors, books_user.publish_date, year, price, quality FROM books_user INNER JOIN ((SELECT id, title, authors, year FROM books WHERE $condition) as `books`) ON (books_user.book_id = books.id) WHERE books_user.status=".BPU_STATUS_PUBLISHED." GROUP BY books_user.id) as `bfound` INNER JOIN users ON (bfound.user_id = users.id) INNER JOIN users_campus ON (users_campus.user_id = users.id) INNER JOIN campus ON (campus.id = users_campus.campus_id) GROUP BY bfound.id ORDER BY title ASC,authors ASC, credits DESC LIMIT ".LIMIT." OFFSET ".($offset * LIMIT);

    $q = $db->query($query);
	
    $out = presentQuery($q);	
	die(json_encode($out));	
}

function presentQuery($q)
{
	global $db, $books_quality;
	$out = array();
	while($r = $db->fetch($q))
	{
        if($r['id'] == null)
            continue;
		$id = (int)$r['id'];
		$title = $r['title'];
		$author = $r['authors'];
		$year = (int)$r['year'];
		$price = sprintf("%.02f",((int)$r['price'])/100);
		$date = convertDate($r['publish_date']);
		$realDate = date("d/m/Y H:i:s", $r['publish_date']);
		$quality = $books_quality[$r['quality']];
		$where = h_clean(@$r['cnames']);

		if(mb_strlen($title)>MAX_LENGTH_TITLE)
			$title = mb_substr($title,0,MAX_LENGTH_TITLE,'UTF-8')."...";
		if(mb_strlen($author)>MAX_LENGTH_AUTHOR)
			$author = mb_substr($author,0,MAX_LENGTH_AUTHOR,'UTF-8')."...";

		$out[] = array(
			"id" => $id,
			"title" => h_clean(utf8_encode($title)),
			"author" => h_clean(utf8_encode($author)),
			"year" => $year,
			"price" => $price,
			"date" => $date,
			"realdate" => $realDate,
			"where" => $where,
			"quality" => $quality,
		);
	}
	return $out;
}

function checkISBN($str)
{
	if(!@eregi("^[0-9\\-\\.\\_ ]+$", $str))
		return false;
	
	$isbn = cleanISBN($str);
	if(strlen($isbn)==10 || strlen($isbn)==13)
		return $isbn;
	return false;
}

function txt2arr($text)
{
	$arr = array();
	$buf = "";
	for($i=0;$i<strlen($text);$i++)
	{
		$c = ord($text[$i]);
		if( ($c>=48 && $c<=57) || ($c>=65 && $c<=90) || ($c>=97 && $c<=122) )
			$buf.= ($text[$i]);
		else if(strlen($buf)>2)
		{
			$arr[] = '*'.$buf.'*';
			$buf = "";
		}
	}
	if(strlen($buf)>2)
		$arr[] = '*'.$buf.'*';
		
	for($i=0;$i<count($arr);$i++)
		$arr[$i] = '"'.$arr[$i].'"';
	return $arr;
}
?>
