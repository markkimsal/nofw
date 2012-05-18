<?php

class Metrofw_Router {

	public $cycles = 0;
	public function analyze(&$request) {

		if ($request->requestedUrl == '' && $this->cycles == 0) {
			$this->cycles++;
			//let's stack ourselves at the end
			associate_iCanHandle('analyze',  'metrofw/router.php');
			return;
		}

		$url = $request->requestedUrl;
		if (strpos($url, '/dologin') === 0) {
			$request->moduleUrl  = 'login';
			$request->moduleName = 'login';
			associate_iCanHandle('authenticate', 'metrou/authenticator.php');
			associate_iCanOwn('output', 'metrofw/redir.php');
		} else {
			$parts = explode('/', $url);
			if (isset($parts[1])) {
				$request->moduleName = $parts[1];
				$request->moduleUrl  = $parts[1];
				associate_iCanHandle('analyze',  $parts[1].'/main.php');
				associate_iCanHandle('resources',  $parts[1].'/main.php');
				associate_iCanHandle('authenticate',  $parts[1].'/main.php');
				associate_iCanHandle('process',  $parts[1].'/main.php');
				associate_iCanHandle('output',  $parts[1].'/main.php');
			}
		}
	}
}

/**
 * Build a URL the same way this router analyzes one.
 */
function m_url($https=0) {
	static $baseuri;
	if (!$baseuri) {
		$baseuri = associate_get('baseuri');
	}

	if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']== 'on') || $https>0) {
		return 'https://'.$baseuri;
	} else {
		return 'http://'.$baseuri;
	}
}
