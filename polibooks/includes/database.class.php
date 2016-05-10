<?php

class Database
{
	private $cn = null;
	public function connect($host, $username, $password, $database)
	{
		$this->cn = mysql_connect($host, $username, $password) or die(mysql_error());
		mysql_select_db($database, $this->cn) or die(mysql_error());
	}
	
	public function escape($string)
	{
		return mysql_real_escape_string($string);
	}
	
	public function query($string)
	{
		$q = mysql_query($string, $this->cn);
		if($q===false) die(mysql_error());
		return $q;
	}
	
	public function fetch($query)
	{
		return mysql_fetch_array($query);
	}
	
	public function rows($query)
	{
		return mysql_num_rows($query);
	}

	public function logMessage($request, $response)
	{
		$request = $this->escape($request);
		$response = $this->escape($response);

//		$this->query("INSERT INTO temp_fb VALUES(0,'$request','$response',".time().")");
	}

	// Store a book into database or retreive it if already exists
	public function storeBook($isbn10, $isbn13, $title, $publishedDate, $pageCount, $authors, $thumbnail, $addedManually=false)
	{
		$isbn10 		= $this->escape($isbn10);
		$isbn13			= $this->escape($isbn13);
		$title			= $this->escape($title);
		$thumbnail		= $this->escape($thumbnail);
		$pageCount 		= (int) $pageCount;

		$addedManually = (int) $addedManually;
		if (!$addedManually)
			$title=utf8_decode($title);
		// build SQL Condition
		$condition="";
		if(strlen($isbn10)==10)
			$condition="isbn10='$isbn10'";
		if(strlen($isbn13)==13)
		{
			if(strlen($condition)>0)
				$condition .= " OR";
			$condition .= " isbn13='$isbn13'";
		}
		
		$q = $this->query("SELECT * FROM books WHERE $condition");
		if($this->rows($q)>0)
		{
			$r = $this->fetch($q);
			return $r['id'];
		}
		
		//convert publish date to unix time
		list($py, $pm, $pd) = sscanf($publishedDate, "%d-%d-%d");
		$pubDate = mktime(6,6,6,$pm,$pd,$py);
		
		//thumbnail
		if(strlen($thumbnail)>5) 
			$thumbnail = "'$thumbnail'";
		else 
			$thumbnail = "null";
			
		//authors
		if(is_array($authors))
		{
			sort($authors);
			$authors_list = implode(",", $authors);
		}
		else
			$authors_list = trim($authors);
			
		$authors_list = $this->escape(utf8_decode($authors_list));
			
		$this->query("INSERT INTO books VALUES(0,  '$title', '$authors_list', '$isbn10','$isbn13', $pageCount, '$publishedDate', $thumbnail, $addedManually)");

		$book_id = mysql_insert_id();
		return $book_id;
	}
}

?>
