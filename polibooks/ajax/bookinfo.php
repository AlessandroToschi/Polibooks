<?php
define("BASE_PATH", "..");
require_once("../common.php");

$isbn = cleanISBN(@$_GET['isbn']);
$len = strlen($isbn);

if($len==10 || $len==13)
{
	$book = JSONGetBook($isbn);
	if($book === false)
		newbook();
	else
		echo $book;
}
else
	error();

function error()
{
	die("ISBN non trovato");
}
function newbook()
{
	global $template;

	die($template->getFile("newbook"));
}
function JSONGetBook($isbn)
{
	global $db, $template;

	$isbn = cleanISBN($isbn);
	$q = $db->query("SELECT * FROM books WHERE isbn10='$isbn' OR isbn13='$isbn'");
	if($db->rows($q))
	{
		$r = $db->fetch($q);
		$title = $r['title'];
		$authors = $r['authors'];
		$pageCount = (int)@$r['page_count'];
		$publishedDate = (int)@$r['year'];
		$thumbnail = @$r['thumbnail'];
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
			$authors = @$v['authors'];
			$pageCount = (int)@$v['pageCount'];
			$publishedDate = (int)@$v['publishedDate'];
			$thumbnail = @$v['imageLinks']['thumbnail'];
		}
	}
	
	if($pageCount<=0) $pageCount = "???";
	if($publishedDate<=0) $publishedDate = "???";

	if(strlen($thumbnail)<2)
		$thumbnail = "/img/noimg.png";		

	if(is_array($authors))
		$authors = @implode(",", $authors);

	$ret = $template->getFile("bookinfoshow");
	$ret = $template->setVariables(	$ret, array(	
		"TITLE" => h_clean($title),
		"AUTHOR" => h_clean($authors),
		"PAGES" => $pageCount,
		"YEAR" => $publishedDate, 
		"IMAGE" => $thumbnail
	));
	return $ret;
}
?>
