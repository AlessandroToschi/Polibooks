<?php

class Template
{
	private $path;
	public function __construct()
	{
		$this->path = "templates/";
	}
	
	public function getFile($name)
	{
        //		$file = apc_fetch("tplate_$name");
        $file = false;
		if($file === false)
		{
			$file = file_get_contents(BASE_PATH."/".$this->path . $name . ".htm");
//			apc_store("tplate_$name", $file);
		}
		return $file;
	}

	public function setVariables($text, $var)
	{
		if(is_array($var))
			foreach($var as $k=>$v)
				$text=str_replace('{'.strtoupper($k).'}',$v,$text);
				
		return $text;
	}

	public function getSite($theme, $title, $content, $menu=null)
	{
		global $language, $user;

		$out = $this->getFile("header");
/*		if($menu == null)
			if($theme==THEME_BASE)
				$mid = $this->getFile("basemain");
			else
				$mid = $this->getFile("singlemain");
		else
			$mid = $this->getFile("doublemain");

		$mid = str_replace("{MAIN}", $content, $mid);
		
		if($menu != null)
			$mid = str_replace("{MENU}", $menu, $mid);
*/
		$out .= $content;
		$out .= $this->getFile("bottom");

		$genmsg = "";
		if(isset($_SESSION['showMessage']))
        {
            $genmsg .= '<div class="alert alert-success alert-dismissable" role="alert" align="center">';
            $genmsg .='<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'.$_SESSION['showMessage'].'</div>';
			unset($_SESSION['showMessage']);
		}
		
		if(isset($_SESSION['errorMessage']))
        {
            $genmsg .= '<div class="alert alert-danger alert-dismissable" role="alert" align="center">';
            $genmsg .='<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'.$_SESSION['errorMessage'].'</div>';
			unset($_SESSION['errorMessage']);
		}
		$out = $this->setVariables($out, array(
			"TITLE" => $title, 
			"GENERALMESSAGE"=> $genmsg, 
			"LOGIN_OR_LOGOUT" => User::getFirstLineMessage(),
			"LANG_COPYRIGHT" => getCopyrightBar()
		));

		$out = $this->setVariables($out, $language->getData());
		return $out;
	}
}

?>
