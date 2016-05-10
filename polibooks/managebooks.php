<?php
require_once("common.php");
global $user;
if(!checkPermissions(USER_AUTHED))
	redirect("index.php");

if(isset($_POST['yes']))
{
	$id = (int)@$_POST['id'];
	$name = "book$id";
	
	if(!antiCSRF($name)) 
	{
		setMessage("Richiesta non valida");
		redirect("?");
	}
	switch(@$_POST['mode'])
	{
		case "delete":
			if($user->removeBook($id))
			{
				setMessage("Libro cancellato con successo");
				redirect("?");
			}
			else
			{
				setMessage("Errore nella cancellazione del libro");
				redirect("?");
			}
			break;
		case "sold":
			if($user->markBookAsSold($id))
			{
				setMessage("Libro venduto. E rimosso dalla lista");
				redirect("?");
			}
			else
			{
				setMessage("Errore nell'operazione");
				redirect("?");
			}
			break;
	}
}
else
{
	$id = (int)@$_GET['id'];
		
	if($id!=0)
	{
		if(!$user->haveBook($id))
		{
			setErrorMessage("ID non valido");
			redirect("?");
		}

		switch(@$_GET['mode'])
		{
			case "delete": 
				request("book$id", "Sei sicuro di voler cancellare il libro?", 
						array(	"mode" => "delete", "id" => $id)); 
				break;
			case "sold":
					request("book$id", "Sei sicuro di voler marcare il libro come \"venduto\"?\nIn questo modo sar&agrave; come cancellato, ma se qualcuno si fosse salvato il link, risulter&agrave; venduto!", array("mode"=>"sold", "id"=>$id));
				break;
			default: show(); break;
		}
	}
}

show();

function createButtons($id)
{
		return 	'<a href="?mode=delete&id='.$id.'"><button class="btn btn-danger btn-block">Elimina</button></a><br>'.
				'<a href="?mode=sold&id='.$id.'"><button class="btn btn-primary btn-block">Segna come venduto</button></a><br>';
}

function show()
{
	global $template, $user, $db, $books_quality;
	$qx = 	"SELECT books_user.id, title, authors, year, isbn10, isbn13, price, quality, publish_date ".
			"FROM books, books_user WHERE books.id = books_user.book_id AND user_id = ".$user->getId()." AND status=".BPU_STATUS_PUBLISHED;
	$q = $db->query($qx);

	if($db->rows($q)>0)
	{
		$rows = "";
		while($r = $db->fetch($q))
		{
			$title = h_clean($r['title']);
			$author = h_clean($r['authors']);
			$year = (int) $r['year'];
			$isbn = h_clean($r['isbn10'])."<br />".h_clean($r['isbn13']);
			$price = "&euro; ".h_clean( ($r['price'])/100);
			$quality = $books_quality[(int)$r['quality']];
			$publish_date = date("H:i d/m/Y", $r['publish_date']);

			$rows.= "<tr><td>$title</td><td>$author</td><td>$year</td><td>$isbn</td><td>$price</td><td>$quality</td><td>$publish_date</td><td>";

			$rows.=createButtons((int)$r['id']);
			$rows.="</td></tr>";
		}

		$main = $template->getFile("managebooks");
		$main = $template->setVariables($main, array("ROWS"=>$rows));
	}
	else
		$main = '<span class="lead">Non hai ancora venduto nessun libro!</span><br>Clicca <a href="/sell.php">qua</a> per venderne uno.<br>';
	echo $template->getSite(THEME_SINGLE, "Gestisci libri", $main);
}

?>

