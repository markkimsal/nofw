<?php

class Example_nofw_Analyze_sapi_http {

	public function analyze(&$request) {
		$params = $_REQUEST;
		$get = $_GET;
		$request->sapiType = 'http';
		if (array_key_exists('PATH_INFO', $_SERVER) && $_SERVER['PATH_INFO']!='') { 		
			$request->requestedUrl = $_SERVER['PATH_INFO'];

			if (substr($_SERVER['PATH_INFO'],-1) == '/' ) {
				$parts = explode("/",substr($_SERVER['PATH_INFO'],1,-1));
			} else {
				$parts = explode("/",substr($_SERVER['PATH_INFO'],1));
			}
			$request->mse =$parts[0];
			array_shift($parts);
			foreach($parts as $num=>$p) { 
				//only put url parts in the get and request
				// if there's no equal sign
				// otherwise you get duplicate entries "[0]=>foo=bar"
				if (!strstr($p,'=')) {
					$p = rawurldecode($p);
					$params[$num] = $p;
					$get[$num] = $p;
				} else {
					@list($k,$v) = explode("=",$p);
					if ($v!='') { 
						$k = rawurldecode($k);
						$v = rawurldecode($v);
						$params[$k] = $v;
						$get[$k] = $v;
					}
				}
			}
		}	

		$request->vars = $params;
		$request->getvars = $get;
		$request->postvars = $_POST;

		// get the base URI 
		// store in the template config area for template processing

		$path = explode("/",$_SERVER['SCRIPT_NAME']);
		array_pop($path);	
		$path = implode("/",$path);
		$uri = $_SERVER['HTTP_HOST'].$path.'/';
		$request->baseUri = $uri;


		/**
		 * determine if ajax
		 */
		if (in_array( 'xhr', array_keys($request->vars),TRUE)) {
			$request->isAjax = TRUE;
		} else {
			$request->isAjax = FALSE;
		}
	}
}
