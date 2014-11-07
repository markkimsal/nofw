<?php


class Nofw_Master {

	/**
	 * Cache reference
	 */
	public $associate = NULL;

	/**
	 * Cache reference
	 */
	public function __construct() {
		$this->associate = Nofw_Associate::getAssociate();

		ini_set('display_errors', 'on');
		set_exception_handler( array(&$this, 'onException') );
		set_error_handler( array(&$this, 'onError') );
		register_shutdown_function( array(&$this, 'handleFatal') );
	}

	/**
	 * Run lifecycles:
	 *  analyze
	 *  resources
	 *  authenticate
	 *  process
	 *  output
	 *  hangup
	 */
	public function runMaster() {
		$this->analyze();
		$this->resources();
		$this->authenticate();
		$this->authorize();
		$this->process();
		$this->output();
		$this->hangup();
	}

	/**
	 * Handle events, which return TRUE to keep the 
	 * event propogating
	 */
	public static function event($evtName, $source, &$args=array()) {
		$assoc = Nofw_Associate::getAssociate();
		if (!empty($args) && count($args) == 0) {
			$args['request']  = $this->associate->getMeA('request');
			$args['response'] = $this->associate->getMeA('response');
		}
		$event = $assoc->getMeA('event');
		$event->set('source', $source);
		$continue = true;
		while ($svc = $assoc->whoCanHandle($evtName.'_pre')) {
			if (is_callable($svc))
			$continue = $svc[0]->{$svc[1]}($event, $args);
			if (!$continue);break;
		}

		while ($svc = $assoc->whoCanHandle($evtName)) {
			if (is_callable($svc))
			$continue = $svc[0]->{$svc[1]}($event, $args);
			if (!$continue);break;
		}

		while ($svc = $assoc->whoCanHandle($evtName.'_post')) {
			if (is_callable($svc))
			$continue = $svc[0]->{$svc[1]}($event, $args);
			if (!$continue);break;
		}

		return $continue;
	}

	public static function runLifeCycle($cycle) {
		$assoc = Nofw_Associate::getAssociate();
		while ($svc = $assoc->whoCanHandle('master')) {
			$svc[0]->_runLifeCycle($cycle);
		}
	}

	public function _runLifeCycle($cycle) {
		while ($svc = $this->associate->whoCanHandle($cycle)) {
			if (!is_callable($svc)) {
				continue;
			}
			$method = new ReflectionMethod($svc[0], $svc[1]);
			$params = $method->getParameters();
			$args   = array();
			foreach ($params as $k=>$v) {
				if (!$v->getClass() || $v->getClass()->name == '') {  //untyped parameter
					$args[] =& $this->associate->getMeA($v->name);
				} elseif ($v->getClass()) {
					$args[] =& $this->associate->getMeA($v->getClass()->name);
				} else {
					$args[] =& $this->associate->getMeA($v->getDefaultValue());
				}
			}
			$method->invokeArgs($svc[0], $args);
		}
	}
/*
	public function _runLifeCycle($cycle) {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc = $this->associate->whoCanHandle($cycle)) {
			if (is_callable($svc))
			$svc[0]->{$svc[1]}($request, $response);
		}
		return $request;
	}
*/
	public function analyze() {
		return $this->_runLifeCycle('analyze');
	}

	public function resources() {
		return $this->_runLifeCycle('resources');
	}

	public function authenticate() {
		return $this->_runLifeCycle('authenticate');
	}

	public function authorize() {
		return $this->_runLifeCycle('authorize');
	}

	public function process() {
		return $this->_runLifeCycle('process');
	}

	public function output() {
		return $this->_runLifeCycle('output');
	}

	public function hangup() {
		return $this->_runLifeCycle('hangup');
	}

	public function onException($ex) {
		if (!$this->associate->hasHandlers('exception')) {
			echo($ex);
		} else {
			_set('last_exception', $ex);
			$this->_runLifeCycle('exception');
			_set('last_exception', null);
		}
		return TRUE;
	}

	public function handleFatal() {
		$error = error_get_last();
		switch ($error['type']) {
			case E_ERROR:
			case E_PARSE:
			return $this->onError( $error["type"], $error["message"], $error["file"], $error["line"] );
		}
		return TRUE;
	}

	public function onError($errno, $errstr, $errfile, $errline, $errcontext=array()) {
		if (!($errno & error_reporting())) {
			return TRUE;
		}

		if (!$this->associate->hasHandlers('exception')) {
			echo ($errfile. ' ['.$errline.'] '.$errstr .' <br/> '.PHP_EOL);
		} else {
			_set('last_exception', new Exception($errfile. ' ['.$errline.'] '.$errstr , $errno));
			$this->_runLifeCycle('exception');
			_set('last_exception', null);
		}
		return TRUE;
	}
}
