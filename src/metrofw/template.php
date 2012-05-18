<?php
class Metrofw_Template {

	public $scriptList    = array();
	public $styleList     = array();
	public $extraJs       = array();
	public $charset       = 'UTF-8';
	public $headTagList   = array();
	public $baseDir       = '';
	public $baseUri       = '';


	public function output($request) {
		$t = associate_get('t');

		if (isset($request->redir)) {
			associate_iCanOwn('output', 'metrofw/redir.php', 1);
			return;
		}
		$layout = associate_get('template_layout', 'index');

		$templateName = associate_get('template_name', 'webapp01');
		associate_set('template_name', $templateName);
		$this->baseDir  = associate_get('template_basedir', 'local/templates/');
		$this->baseUrl  = associate_get('template_baseuri', 'local/templates/');

		associate_set('template_baseuri', $this->baseUrl);

		associate_set('baseuri', $request->baseUri);

		associate_iCanHandle('template.main', 'metrofw/template.php', 1);

		if (isset($request->sparkMsg) ) {
			associate_iCanHandle('template.sparkmsg', 'metrofw/sparkmsg.php', 1);
		}

		$this->parseTemplate($layout);
	}

	function parseTemplate($layout = 'index') {

		$templateName = associate_get('template_name');
		//scope
		$t = associate_get('t');

		$req = associate_getMeA('request');
		$u = associate_getMeA('user');

		if ($req->isAjax) {
			header('Content-type: application/json');
			echo json_encode($t);
//			$this->doEncodeJson($t);
			return true;
		}

		$templateIncluded = FALSE;
		if ($layout == '') {
			$layout = 'index';
		}
		//try special style, if not fall back to index
		if (!include( $this->baseDir. $templateName.'/'.$layout.'.html.php') ) {
			if(@include($this->baseDir. $templateName.'/index.html.php')) {
				$templateIncluded = TRUE;
			}
		} else {
			$templateIncluded = TRUE;
		}

		if (!$templateIncluded) {
			$errors = array();
			$errors[] = 'Cannot include template.';
			associate_set('output_errors', $errors);
			associate_iCanHandle('output', 'metrofw/terrors.php');
			return true;
		}
	}

	public function template($request, $section) {
		if (isset($request->output)) {
			return $request->output;
		}

		ob_start();
		@include($this->baseDir.associate_get('template.main.file', $request->appName.'/main.html.php'));
		return ob_get_contents() . substr( ob_end_clean(), 0, 0);
	}

	/**
	 * Ask for who can handle the given section
	 */
	static public function parseSection($section) {
		$associate = Nofw_Associate::getAssociate();
		$request = $associate->getMeA('request');
		$output = '';
		while ($svc =  $associate->whoCanHandle($section)) {
			$output .= $svc->template($request, $section);
		}
		return $output."\n";
	}


	/**
	 * As associate if a section has anyone listening
	 */
	static public function hasHandlers($section) {
		$associate = Nofw_Associate::getAssociate();
		return (isset($associate->serviceList[$section]) &&
			is_array($associate->serviceList[$section]) &&
			count($associate->serviceList[$section]) > 0);
	}
}

function sitename() {
	return associate_get('sitename', 'Metro');
}

/**
 * wrapper for static function
 */
function m_turl($https=-1) {
	static $baseUri;
	static $templateName;
	static $templatePath;
	if (!$baseUri) {
		$baseUri = associate_get('baseuri');
	}
	if (!$templatePath) {
		$templatePath = associate_get('template_baseuri');
	}
	if (!$templateName) {
		$templateName = associate_get('template_name');
	}
	$end = $baseUri.$templatePath.$templateName.'/';

	if ($https === 0) {
		return 'http://'.$end;
	} else if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || $https>0) {
	var_dump($https);return;
		return 'https://'.$end;
	} else {
		return 'http://'.$end;
	}
}

