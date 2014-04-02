<?php
namespace controller ;
Class Csrf {


	static function store_in_session($key,$value){
		if (isset($_SESSION))
		{
			$_SESSION[$key]=$value;
		}
	}


	static function unset_session($key){
		$_SESSION[$key]=' ';
		unset($_SESSION[$key]);
	}

	static function get_from_session($key){
		if (isset($_SESSION))
		{
			return $_SESSION[$key];
		}
		else {  return false; } //no session data, no CSRF risk
		}

	static function csrfguard_generate_token($unique_form_name){
	if (function_exists("hash_algos") and in_array("sha512",hash_algos()))
	{
		$token=hash("sha512",mt_rand(0,mt_getrandmax()));
	}
	else
	{
		$token=' ';
		for ($i=0;$i<128;++$i)
		{
			$r=mt_rand(0,35);
			if ($r<26)
			{
				$c=chr(ord('a')+$r);
			}
			else
			{ 
				$c=chr(ord('0')+$r-26);
			} 
			$token.=$c;
		}
	}
	self::store_in_session($unique_form_name,$token);
	return $token;
	}



	static function csrfguard_validate_token($unique_form_name,$token_value){
		$token=self::get_from_session($unique_form_name);
		if ($token===false)
		{
			return true;
		}
		elseif ($token===$token_value)
		{
			$result=true;
		}
		else
		{ 
			$result=false;
		} 
		self::unset_session($unique_form_name);
		return $result;
	}


	// ---------------------------  Fonction appelée dans view.php ----------------------------

static function csrfguard_replace_forms($form_data_html) {
		$count=preg_match_all("/<form(.*?)>(.*?)<\\/form>/is",$form_data_html,$matches,PREG_SET_ORDER);
		if (is_array($matches))
		{
			foreach ($matches as $m)
			{
				if (strpos($m[1],"nocsrf")!==false) { continue; }
				$name="CSRFGuard_".mt_rand(0,mt_getrandmax());
				$token=self::csrfguard_generate_token($name);
				$form_data_html=str_replace($m[0],
				"<form{$m[1]}>
				<input type='hidden' name='CSRFName' value='{$name}' />
				<input type='hidden' name='CSRFToken' value='{$token}' />{$m[2]}</form>",$form_data_html);
		}
	}
	return $form_data_html;
}

	function csrfguard_start(){
		if (count($_POST))
		{
			if ( !isset($_POST['CSRFName']) or !isset($_POST['CSRFToken']) )
			{
				trigger_error("No CSRFName found, probable invalid request.",E_USER_ERROR);		
			} 
			$name =$_POST['CSRFName'];
			$token=$_POST['CSRFToken'];
			if (!csrfguard_validate_token($name, $token))
			{ 
				$this->app->halt();
			}
		}
	}

}
?>