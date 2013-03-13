<?php
class Example_Router {

	public function analyze(&$request) {

		$url = $request->requestedUrl;

		$request->urlParts = $parts = explode('/', $url);
		if (strpos($url, '/dologin') === 0) {
			$request->appName = 'login';
//			associate_iCanHandle('authenticate', 'example/authenticator.php');
		} else {
			if (isset($parts[1])) {
				$request->appName = $parts[1];
				associate_iCanHandle('analyze',       $parts[1].'/main.php');
				associate_iCanHandle('resources',     $parts[1].'/main.php');
				associate_iCanHandle('authenticate',  $parts[1].'/main.php');
				associate_iCanHandle('process',       $parts[1].'/main.php');
				associate_iCanHandle('output',        $parts[1].'/main.php');
			}
		}
	}
}

/**
 * Build a URL the same way this router analyzes one.
 */
function r_url($https=0) {
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
