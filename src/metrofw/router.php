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

		associate_set('baseuri', $request->baseUri);

		//not using rewrite?
		if ($request->rewrite == FALSE) {
			associate_set('appuri', $request->baseUri. $request->script.'/');
		} else {
			associate_set('appuri', $request->baseUri );
		}


		if (strpos($url, '/dologin') === 0) {
			$request->appUrl  = 'login';
			$request->appName = 'login';
			associate_iCanHandle('authenticate', 'metrou/authenticator.php');
			associate_iCanOwn('output', 'metrofw/redir.php');
		}

		if (@$request->vars[0] == 'dologout') {
			$request->appUrl  = 'logout';
			$request->appName = 'logout';
			associate_iCanHandle('authenticate', 'metrou/logout.php');
			return;
		}

		if (strpos($url, '/hello') === 0) {
			$request->appUrl  = 'hello';
			$request->appName = 'hello';
//			associate_iCanHandle('authenticate', 'metrou/authenticator.php');
			associate_iCanOwn('output', 'test/helloworld.php');
			return;
		}


		$parts = explode('/', $url);
		if (!isset($parts[1])) {
			$parts[1] = 'main';
		}

		//handle "pages"
		if (substr($parts[1], -5) == '.html') {
			$request->appName = 'content';
			$request->appUrl  = $parts[1];
			associate_iCanHandle('analyze',  'content/main.php');
			associate_iCanHandle('resources',  'content/main.php');
			associate_iCanHandle('authenticate',  'content/main.php');
			associate_iCanHandle('process',  'content/main.php');
			associate_iCanHandle('output',  'content/main.php', 1);
			return;
		}

		$request->appName = $parts[1];
		$request->appUrl  = $parts[1];
		associate_iCanHandle('analyze',  $parts[1].'/main.php');
		associate_iCanHandle('resources',  $parts[1].'/main.php');
		associate_iCanHandle('authenticate',  $parts[1].'/main.php');
		associate_iCanHandle('process',  $parts[1].'/main.php');
		associate_iCanHandle('output',  $parts[1].'/main.php', 1);
	}

	public function unrouteUrl($app) {
		return $app;
	}

	public function formatArgs($args) {
		if ($args === NULL) {
			return '';
		}
		if (!is_array($args)) {
			return '';
		}

		$v = '';
		foreach ($args as $_k => $_v) {
			$v .= '/'.urlencode($_k).'='.urlencode($_v);
		}
		return $v;
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

/**
 */
function m_appurl($url='', $args=null, $https=-1) {
	static $baseUri;
	static $templateName;
	static $templatePath;
	if (!$baseUri) {
		$baseUri = associate_get('appuri');
	}

	$router = associate_getMeA('router');
	$url  = $router->unrouteUrl($url);
	$url .= $router->formatArgs($args);
	$end  = $baseUri.$url;
	if (substr($end, -1) !== '/') {
		$end .= '/';
	}

	if ($https === 0) {
		return 'http://'.$end;
	} else if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || $https>0) {
		return 'https://'.$end;
	} else {
		return 'http://'.$end;
	}
}

/**
 */
function m_pageurl($url, $args=null, $https=-1) {
	static $baseUri;
	static $templateName;
	static $templatePath;
	if (!$baseUri) {
		$baseUri = associate_get('appuri');
	}

	$router = associate_getMeA('router');
	// *
	$url  = $router->unrouteUrl($url);
	$url .= $router->formatArgs($args);
	$end  = $baseUri.$url;
	// * /

	if ($https === 0) {
		return 'http://'.$end;
	} else if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || $https>0) {
		return 'https://'.$end;
	} else {
		return 'http://'.$end;
	}
}
