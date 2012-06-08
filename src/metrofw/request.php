<?php

class Metrofw_Request {

	public $vars           = array();
	public $getvars        = array();
	public $postvars       = array();
	public $cookies        = array();
	public $isAdmin        = FALSE;
	public $rewrite        = TRUE;
	public $requestedUrl   = '';
	public $moduleName     = '';
	public $serviceName    = '';
	public $eventName      = '';
	public $urlName        = '';
	public $sapiType       = '';
	public $isAjax         = FALSE;
	public $prodEnv        = 'prod';
	public $httpStatus     = '200';


	/**
	 * @return boolean True if this production environment is 'demo'
	 */
	public function isDemo() {
		return $this->isEnv('demo');
	}

	/**
	 * @return boolean True if this production environment is 'test'
	 */
	public function isTest() {
		return $this->isEnv('test');
	}

	/**
	 * @return boolean True if this production environment is 'prod'
	 */
	public function isProduction() {
		return $this->isEnv('prod');
	}

	/**
	 * @return boolean True if this production environment is 'dev'
	 */
	public function isDevelopment() {
		return $this->isEnv('dev');
	}

	/**
	 * @return boolean True if this production environment is $state
	 */
	public function isEnv($state) {
		return $this->prodEnv == $state;
	}

	/**
	 * Return the default session object.
	 *
	 * @return Object   the default session object.
	 */
	public function getSession() {
		return associate_getMeA('session');
	}

	/**
	 * removes effects of Magic Quotes GPC
	 */
	public function stripMagic() {
		@set_magic_quotes_runtime(0);
		// if magic_quotes_gpc strip slashes from GET POST COOKIE
		if (get_magic_quotes_gpc()){
		function stripslashes_array($array) {
		 return is_array($array) ? array_map('stripslashes_array',$array) : stripslashes($array);
		}
		$_GET= stripslashes_array($_GET);
		$_POST= stripslashes_array($_POST);
		$_REQUEST= stripslashes_array($_REQUEST);
		$_COOKIE= stripslashes_array($_COOKIE);
		}
	}

	/**
	 * This method finds a parameter from the GET or POST. 
	 * Order of preference is GET then POST
	 *
	 * @return bool  true if the key exists in get or post
	 */
	public function hasParam($name) {
		if (isset($this->getvars[$name])) {
			return TRUE;
		}
		if (isset($this->postvars[$name])) {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * This method cleans a string from the GET or POST. 
	 * It does *not* escape data safely for SQL.
	 * Order of preference is GET then POST
	 *
	 * @return string
	 */
	public function cleanString($name) {
		if (isset($this->getvars[$name])){
			$val = $this->getvars[$name];
		} else {
			$val = @$this->postvars[$name];
		}
		if ($val == '') {
			return '';
		}
		if (is_array($val)) {
			array_walk_recursive($val, array($this, 'removeCtrlChar'));
		} else {
		   	$this->removeCtrlChar($val);
			$val = (string)$val;
		}
		return $val;

	}

	/**
	 * This method cleans a multi-line string from the GET or POST. 
	 * It does *not* escape data safely for SQL.
	 * Order of preference is GET then POST
	 *
	 * This method allows new line, line feed and tab characters
	 * @return string
	 */
	public function cleanMultiLine($name) {
		if (isset($this->getvars[$name])){
			$val = $this->getvars[$name];
		} else {
			$val = @$this->postvars[$name];
		}
		if ($val == '') {
			return '';
		}
		$allow = array();
		$allow[] = ord("\t");
		$allow[] = ord("\n");
		$allow[] = ord("\r");

		if (is_array($val)) {
			array_walk_recursive($val, array($this, 'removeCtrlChar'), $allow);
		} else {
		   	$this->removeCtrlChar($val, NULL, $allow);
			$val = (string)$val;
		}
		return $val;

	}

	/**
	 * This method cleans an integer from the GET or POST. 
	 * It always returns the result of intval()
	 * Order of preference is GET then POST
	 *
	 * @return int
	 */
	public function cleanInt($name) {
		if (isset($this->getvars[$name])){
			if (is_array($this->getvars[$name])){
				return Cgn::cleanIntArray($this->getvars[$name]);
			}
			return intval($this->getvars[$name]);
		} else {
			if (@is_array($this->postvars[$name])){
				return Cgn::cleanIntArray($this->postvars[$name]);
			}
			return intval(@$this->postvars[$name]);
		}
	}

	/**
	 * This method cleans a float from the GET or POST. 
	 * It always returns the result of floatval()
	 * Order of preference is GET then POST
	 *
	 * @return float
	 */
	public function cleanFloat($name) {
		if (isset($this->getvars[$name])){
			if (is_array($this->getvars[$name])){
				return Cgn::cleanFloatArray($this->getvars[$name]);
			}
			return floatval($this->getvars[$name]);
		} else {
			if (@is_array($this->postvars[$name])){
				return Cgn::cleanFloatArray($this->postvars[$name]);
			}
			return floatval(@$this->postvars[$name]);
		}
	}

	/**
	 * This method cleans a string from the GET or POST, removing any HTML tags. 
	 * It does *not* escape data safely for SQL.
	 * Order of preference is GET then POST
	 *
	 * @return string
	 */
	public function cleanHtml($name) {
		if (isset($this->getvars[$name])){
			return (string)strip_tags(urldecode($this->getvars[$name]));
		} else {
			return (string)@strip_tags(urldecode($this->postvars[$name]));
		}
	}

	/**
	 * Replaces any non-printable control characters with underscores (_).
	 * Can be called with array_walk or array_walk_recursive
	 */
	public function removeCtrlChar(&$input, $key = NULL, $allow = array()) {
		//preg throws an error if the pattern cannot compile
		$len = strlen($input);
		$extra = count($allow);
		for($i = 0; $i < $len; $i++) {
			$hex =ord($input{$i});
			if ($extra && in_array($hex, $allow)) {
				continue;
			}
			if ( ($hex < 32) ) {
				$input{$i} = '_';
			}
			if ($hex == 127 ) {
				$input{$i} = '_';
			}
		}
	}

	public function getUser() {
		return associate_getMeA('user');
	}
}

