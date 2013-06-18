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
			if (is_callable($svc))
			//$svc[0]->analyze($request, $response);
			$svc[0]->{$svc[1]}($request, $response);
		}
		return $request;
	}

	public function resources() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('resources')) {
			if (is_callable($svc))
			$svc[0]->{$svc[1]}($request, $response);
		}
		return $request;
	}

	public function authenticate() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('authenticate')) {
			if (is_callable($svc))
			$svc[0]->{$svc[1]}($request, $response);
		}
		return $request;
	}

	public function authorize() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('authorize')) {
			if (is_callable($svc))
			//$svc->authorize($request, $response);
			$svc[0]->{$svc[1]}($request, $response);
		}
		return $request;
	}

	public function process() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('process')) {
			if (is_callable($svc))
			//$svc->process($request, $response);
			$svc[0]->{$svc[1]}($request, $response);
		}
		return $request;
	}

	public function output() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('output')) {
			if (is_callable($svc))
			$svc[0]->{$svc[1]}($request, $response);
		}
		return $request;
	}

	public function hangup() {
		$request  = $this->associate->getMeA('request');
		$response = $this->associate->getMeA('response');
		while ($svc =  $this->associate->whoCanHandle('hangup')) {
			if (is_callable($svc))
			//$svc->hangup($request, $response);
			$svc[0]->{$svc[1]}($request, $response);
		}
		return $request;
	}
}
