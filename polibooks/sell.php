<?php
require_once("common.php");
if(!checkPermissions(USER_AUTHED))
	redirect("mustlogin.php?to=sell");

if(!isset($_POST['add']))
	form();
else
	add();

function form($error="")
{
	global $template, $books_quality;
	
	$form = $template->getFile("addbook");
	$form = $template->setVariables($form, array(
		"ACTION" => "?",
		"QUALITY" => asOptions($books_quality, "--Scegli--"),
		"YEAR" => date("Y"),
	));
    if(strlen($error) > 0)
        setErrorMessage($error);
	echo $template->getSite(THEME_SINGLE, "Vendi un libro", $form);
	die();
}

function add()
{
	global $template, $db, $user;
	
	$isbn = gpc2db($_POST['isbn']);
	$price = @floatval(str_replace(",",".",gpc2db($_POST['price'])));
	$quality = (int) @$_POST['quality'];

	$price_commas=0;
	for($i=0;$i<strlen($_POST['price']);$i++)
	{
		$c = $_POST['price'][$i];
		if($c=='.' || $c==',')
			$price_commas++;
	}

    //TODO URGENTE: controllo se è valido 
    // l'isbn
    
    $book_id = getBook($isbn);
	if($book_id===false)
	{
		$ntitle = gpc2db(trim(@$_POST['new_title']));
		$nauthor = gpc2db(trim(@$_POST['new_author']));
		$npages = (int)@$_POST['new_pages'];
		$nyear = (int)@$_POST['new_year'];

		if(strlen($ntitle)==0)
			form("Il titolo inserito non è valido");
		if(strlen($nauthor)==0)
			form("L'autore inserito non è valido");
		if($npages<=0)
			form("Numero di pagine non valido");
		if($nyear<=0 || $nyear>date("Y"))
			form("Anno non valido");

		$isbn10 = $isbn13 = "";
		if(strlen($isbn)==10)
			$isbn10 = $isbn;
		else if(strlen($isbn)==13)
			$isbn13 = $isbn;
		else
			form("ISBN non valido");

		$book_id = $db->storeBook($isbn10, $isbn13, $ntitle, $nyear, $npages, $nauthor, "", true);
	}
	if(is_nan($price) || $price<0 || ($price==0 && !@eregi("^0+([,.]0+)?$",$_POST['price'])) || $price_commas>1)
		form("Prezzo non valido");
	if($quality<1||$quality>5)
		form("Qualità non valida");
	$notes = trim($_POST['note']);
	if(strlen($notes)>300)
		form("Nota troppo lunga");

	if($user->addBook($book_id, $price, $quality,$notes))
	{
		setMessage("Libro aggiunto con successo");
		redirect("index.php");
	}
	else
		form("Errore durante l'aggiunta del libro.");
		
	die();
}

echo $template->getSite(THEME_MULTIPLE,"PoliBooks", "A", "B");
?>
