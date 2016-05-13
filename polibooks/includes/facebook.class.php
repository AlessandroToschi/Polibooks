<?php

require_once("database.class.php");
require_once(BASE_PATH."/config.php");

class Facebook
{
	private $token = "";
	
	public function __construct()
	{
		if(isset($_SESSION['fb_token']))
			$this->token = $_SESSION['fb_token'];
	}
	
	/* gestisce l'autenticazione con facebook */
	function processAuth($state, $code)
	{
		global $config, $db;

		if($this->checkState())
		{
			$code = urlencode($code);
			
			$f = do_get_request("https://graph.facebook.com/oauth/access_token?client_id=".$config['app_id'].
									"&redirect_uri=".$config['callback'] .
									"&client_secret=".$config['app_secret']."&code=$code");
			parse_str($f, $params);
			$token = $params['access_token'];

			if(strlen($token)>0)
			{		
				$_SESSION['fb_state']=null;
				$this->token = $token;
				$_SESSION['fb_userdata'] = $this->doGraphGet("me");

				if(!$_SESSION['fb_userdata'])
					return false;

				$_SESSION['fb_token']=$token;

				return true;
			}
		}
		
		return false;
	}
	
	public function getAuthUrl()
	{
		global $config;
		$permissions = 	"email";
		
		$_SESSION['fb_state'] = md5(uniqid(rand(), TRUE));
		return	"https://www.facebook.com/dialog/oauth?".
				"client_id=".$config['app_id'].
				"&scope=$permissions".
				"&redirect_uri=".$config['callback'].
				"&state=".$_SESSION['fb_state'];
	}
	
	public function checkState()
	{
		if(@$_SESSION['fb_state'] && (@$_SESSION['fb_state'] === @$_REQUEST['state']))
			return true;
		return false;
	}
	
	public function doGraphGet($url, $data = null)
	{
		$f_url = "https://graph.facebook.com/$url?";
		if(is_array($data))
			foreach($data as $k => $v)
				$f_url .= urlencode($k) . "=" . urlencode($v) . "&";
				
		$f_url .= "access_token=".$this->token;
		
		$f = do_get_request($f_url);
		return json_decode($f, true);
	}

	public function doGraphPost($url, $post_data)
	{
		global $db;
		$f_url = "https://graph.facebook.com/$url";
		$post_data["access_token"] = $this->token;

		$out = do_post_request($f_url, $post_data, null);

		return json_decode($out, true);
	}

	function getAccessToken()
	{
		return $this->token;
	}

	function setAccessToken($token)
	{
		$this->token = $token;
	}
}

?>
