<?php


class Corefw_Master {

	public $associate = NULL;


	public function __construct() {
		$this->associate = Corefw_Associate::getAssociate();
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
			$svc->analyze($request);
		}
		return $request;
	}

	/**
	 * @return a user
	 */
	public function resources() {
		$request = $this->associate->getMeA('request');
	}

	public function authenticate() {
	}

	public function authorize() {
	}

	public function process() {
	}

	public function output() {
		$request = $this->associate->getMeA('request');
		while ($svc =  $this->associate->whoCanHandle('output')) {
			$svc->output($request);
		}
		return $request;

	}

	public function hangup() {
	}
}
