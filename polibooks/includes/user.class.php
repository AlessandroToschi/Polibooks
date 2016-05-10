<?php

require_once(BASE_PATH."/common.php");

class User
{
	private $userData;
	private $userAuthed;

	static function getUserInfo($fbid, $name, $surname)
	{
		global $db;
		$fbid = $db->escape($fbid);
		#$q = $db->query("SELECT * FROM users WHERE fb_id=$fbid");
		$q = $db->query("SELECT * FROM users WHERE name='$name' AND surname='$surname'");
		if($db->rows($q)>0)
			return $db->fetch($q);
		return false;
	}

	static function getFirstLineMessage()
	{
		global $user;

		if($user->isAuthed())
            return <<< __HTML__
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Account<span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="/polibooks/editprofile.php">Modifica Profilo</a></li>
                                <li><a href="/polibooks/managebooks.php">Gestisci i tuoi libri</a></li>
                                <li><a href="/polibooks/credits.php">I tuoi crediti e <i>Il Link</i></a></li>
                                <li><a href="/polibooks/auth.php?signout">Logout</a></li>
                            </ul>
                        </li>
__HTML__;
		else if(@$_SESSION['reg_step'] == 1)
			return '<li><a href="/polibooks/auth.php?signout">Logout</a></li>';
		else
			return '<li><a href="/polibooks/auth.php">Log In</a></li>';
	}

	public function __construct()
	{
		if(isset($_SESSION['userData']))
		{
			$this->userData = $_SESSION['userData'];
			$this->userAuthed = true;
		}
		else
			$this->userAuthed = false;
	}

	public function getRawData($field)
	{
		return $this->userData[$field];
	}

	public function isAuthed()
	{
		return $this->userAuthed;
	}

	public function getData()
	{
		return $this->userData;
	}

	public function getId()
	{
		if(!$this->isAuthed()) return 0;
		return $this->userData['id'];
	}
	public function getName()
	{
		if(!$this->isAuthed()) return null;
		return $this->userData['name'];
	}
	public function getSurname()
	{
		if(!$this->isAuthed()) return null;
		return $this->userData['surname'];
	}
	public function getGender()
	{
		if(!$this->isAuthed()) return null;
		return $this->userData['gender'];
	}
	public function getEmail()
	{
		if(!$this->isAuthed()) return null;
		return $this->userData['email'];
	}
	public function getPhone()
	{
		if(!$this->isAuthed()) return null;
		return $this->userData['phone'];
	}
	
	public function getSettings()
	{
		if(!$this->isAuthed()) return null;
		return $this->userData['settings'];
	}

	public function getShareLink()
	{
		if(!$this->isAuthed()) return null;
		return "http://www.polibooks.it/?k_x=".$this->getId().$this->userData['authCode'];
	}

	public function getCredits()
	{
		if(!$this->isAuthed()) return 0;
		$this->reload();
		return $this->userData['credits'];
	}

	public function getCampusList()
	{
		global $db;
		if(!$this->isAuthed()) return null;
		$q = $db->query("SELECT campus_id FROM users_campus WHERE user_id = ".$this->getId());
		
		$campus = array();
		while($r = $db->fetch($q))
			$campus[] = $r['campus_id'];
			
		return $campus;
	}

	public function haveBook($book_id)
	{
		global $db;
		if(!$this->isAuthed()) return false;

		$q = $db->query("SELECT COUNT(*) FROM books_user WHERE user_id=".$this->getId()." AND id=$book_id");
		$r = $db->fetch($q);
		$n = $r[0];
		if($n>0)
			return true;
		return false;
	}
	
	public function canOperateBook($book_id)
	{
		global $db;
		if(!$this->isAuthed()) return false;

		$q = $db->query("SELECT COUNT(*) FROM books_user WHERE user_id=".$this->getId()." AND id=$book_id AND status =".BPU_STATUS_PUBLISHED);
		$r = $db->fetch($q);
		$n = $r[0];
		if($n>0)
			return true;
		return false;
	}
	
	public function markBookAsSold($book_id)
	{
		global $db;
		if(!$this->canOperateBook($book_id))
			return false;
		
		$db->query("UPDATE books_user SET status = ".BPU_STATUS_SOLD." WHERE id=$book_id");
		return true;
	}
	
	public function removeBook($book_id)
	{
		global $db;
		if(!$this->canOperateBook($book_id))
			return false;
		
		$db->query("UPDATE books_user SET status = ".BPU_STATUS_REMOVED." WHERE id=$book_id");
		return true;
	}

	public function addBook($realBookId, $price, $quality, $notes)
	{
		global $db;
		if(!$this->isAuthed() || $this->getId()==0) return false;
		
		$price=(int)($price*100);
		$notes=$db->escape(trim($notes));

		if(strlen($notes)>0)
			$notes = "'$notes'";
		else
			$notes = "NULL";

		$db->query("INSERT INTO books_user VALUES(0,".$this->getId().",$realBookId,$price,$quality,".BPU_STATUS_PUBLISHED.",".time().",'".$_SERVER['REMOTE_ADDR']."','', $notes)");
		return true;
	}

	public function attach($userData)
	{
		$this->userData = $userData;
		$_SESSION['userData'] = $userData;
		if($userData!=null)
			$this->userAuthed = true;
		else
			$this->userAuthed = false;
	}

	public function reload()
	{
		global $db;
		if(!$this->isAuthed())
			return false;

		$q = $db->query("SELECT * FROM users WHERE id=".$this->getId());
		if($db->rows($q)!=1)
		{
			$this->attach(null);
			return false;
		}

		$r = $db->fetch($q);
		$this->attach($r);
	}
}

?>
