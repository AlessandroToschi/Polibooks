<?php

class Language
{
	private $curLang = null;
	public function __construct($lang)
	{
		if(!@eregi("^[a-z][a-z]$",$lang))
			die("LANGUAGE.PHP ERROR!");

		include(BASE_PATH."/languages/$lang.php");
		$this->curLang = $langdata;
	}
	public function getData()
	{
		return $this->curLang;
	}
}

?>
