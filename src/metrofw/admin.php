<?php


class Metrofw_Admin {

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
		$request = $this->associate->getMeA('request');
		while ($svc = $this->associate->whoCanHandle('analyze')) {
			if (is_callable(array($svc, 'analyze')))
			$svc->analyze($request);
		}
		return $request;
	}

	/**
	 * @return a user
	 */
	public function resources() {

		associate_set('template_name', 'admin01');

		$request = $this->associate->getMeA('request');
		while ($svc =  $this->associate->whoCanHandle('resources')) {
			if (is_callable(array($svc, 'resources')))
			$svc->resources($request);
		}
		return $request;
	}

	public function authenticate() {
		$request = $this->associate->getMeA('request');
		while ($svc =  $this->associate->whoCanHandle('authenticate')) {
			if (is_callable(array($svc, 'authenticate')))
			$svc->authenticate($request);
		}
		return $request;
	}

	public function authorize() {
		$request = $this->associate->getMeA('request');
		while ($svc =  $this->associate->whoCanHandle('authorize')) {
			if (is_callable(array($svc, 'authorize')))
			$svc->authorize($request);
		}
		return $request;
	}

	public function process() {
		$request = $this->associate->getMeA('request');
		while ($svc =  $this->associate->whoCanHandle('process')) {
			if (is_callable(array($svc, 'process')))
			$svc->process($request);
		}
		return $request;
	}

	public function output() {
		$request = $this->associate->getMeA('request');
		while ($svc =  $this->associate->whoCanHandle('output')) {
			if (is_callable(array($svc, 'output')))
			$svc->output($request);
		}
		return $request;
	}

	public function hangup() {
		$request = $this->associate->getMeA('request');
		while ($svc =  $this->associate->whoCanHandle('hangup')) {
			if (is_callable(array($svc, 'hangup')))
			$svc->hangup($request);
		}
		return $request;
	}
}
