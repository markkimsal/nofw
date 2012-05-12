<?php

class Example_nofw_Analyze_sapi_cli {

	public function analyze(&$req) {
		global $argv;
		$req->sapiType = 'cli';

		$get = array();
		//cron.php or index.php from arg list
		@array_shift($argv);
		$req->requestedUrl = implode('/', $argv);
//		$req->mse = $argv[0];
//		@array_shift($argv);

		foreach($argv as $num=>$p) { 
			//only put argv in the get and request
			// if there's no equal sign
			// otherwise you get duplicate entries "[0]=>foo=bar"

			if (!strstr($p,'=')) {
				$argv[$num] = $p;
				$get[$num] = $p;
			} else {
				@list($k,$v) = explode("=",$p);
				if ($v!='') { 
					$argv[$k] = $v;
					$get[$k] = $v;
				}
			}
		}
		$req->getvars = $get;

		/**
		 * determine if ajax
		 */
		if (in_array( 'xhr', array_keys($req->getvars),TRUE)) {
			$req->isAjax = TRUE;
		} else {
			$req->isAjax = FALSE;
		}
	}
}
