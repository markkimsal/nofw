<?php

class Metrofw_Response {

	public $sectionList = array();
	public $headerList  = array();

	public function setHeader($k, $v) {
		$this->headerList[$k] = $v;
	}

	public function __set($k, $v) {
		$this->set($k, $v);
	}

	public function __get($kv) {
		$this->get($k);
	}

	public function set($k, $v) {
		$this->sectionList[$k] = $v;
	}

	public function get($k) {
		if (isset($this->sectionList[$k]))
			return $this->sectionList[$k];
		return null;
	}

	public function has($k) {
		if (isset($this->sectionList[$k]))
			return true;
		return false;
	}

	/**
	 * Set the HTTP status first, in case output buffering is not on.
	 */
	public function output($req) {
		$this->statusHeader($req);
		if (isset($req->redir)) {
			associate_iCanOwn('output', 'metrofw/redir.php', 1);
			return;
		}

		if ($req->isAjax) {
			header('Content-type: application/json');
			echo json_encode($this->sectionList);
		} else {
			associate_iCanHandle('output', 'metrofw/template.php');
		}
	}


	/**
	 * Set the HTTP status header again if output buffering is on
	 */
	public function hangup($req) {
		$this->statusHeader($req);
	}

	public function statusHeader($req) {
		switch ($req->httpStatus) {
			case '200':
			header('HTTP/1.1 200 OK');
			break;

			case '404':
			header('HTTP/1.1 404 File Not Found');
			break;

			case '500':
			case '501':
			header('HTTP/1.1 501 Server Error');
			break;

		} 
	}
}
