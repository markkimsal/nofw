<?php

class Example_nofw_Analyzer {

	public function analyze(&$request) {
		$request->stripMagic();

		$sapi = php_sapi_name();

		switch($sapi) { 

			case "cli":
				associate_iCanHandle('analyze', 'example_nofw/analyze_sapi_cli.php');
			break;

			case "apache":
			case "apache2filter":
			case "apache2handler":
				associate_iCanHandle('analyze', 'example_nofw/analyze_sapi_http.php');
			break;

			case "cgi-fcgi":
			case "cgi":
				associate_iCanHandle('analyze', 'example_nofw/analyze_sapi_cgi.php');
			break;

			default:
				die('unknonwn sapi: '.$sapi);

		}

	}

}
