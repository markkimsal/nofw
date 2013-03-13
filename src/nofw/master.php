<?php


class Nofw_Master {

	public $associate = NULL;


	public function __construct() {
		$this->associate = Nofw_Associate::getAssociate();
	}

	public function runMaster() {
		$this->analyze();
		$this->resources();
		$this->authenticate();
		$this->process();
		$this->output();
		$this->hangup();
	}

	public function analyze() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc = $this->associate->whoCanHandle('analyze')) {
			if (is_callable(array($svc, 'analyze')))
			$svc->analyze($request, $response);
		}
		return $request;
	}

	/**
	 * @return a user
	 */
	public function resources() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('resources')) {
			if (is_callable(array($svc, 'resources')))
			$svc->resources($request, $response);
		}
		return $request;
	}

	public function authenticate() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('authenticate')) {
			if (is_callable(array($svc, 'authenticate')))
			$svc->authenticate($request, $response);
		}
		return $request;
	}

	public function authorize() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('authorize')) {
			if (is_callable(array($svc, 'authorize')))
			$svc->authorize($request, $response);
		}
		return $request;
	}

	public function process() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('process')) {
			if (is_callable(array($svc, 'process')))
			$svc->process($request, $response);
		}
		return $request;
	}

	public function output() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('output')) {
			if (is_callable(array($svc, 'output')))
			$svc->output($request, $response);
		}
		return $request;
	}

	public function hangup() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('hangup')) {
			if (is_callable(array($svc, 'hangup')))
			$svc->hangup($request, $response);
		}
		return $request;
	}
}
