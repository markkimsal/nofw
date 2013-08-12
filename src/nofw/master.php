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
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc = $this->associate->whoCanHandle($cycle)) {
			if (is_callable($svc))
			$svc[0]->{$svc[1]}($request, $response);
		}
		return $request;
	}

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
}
