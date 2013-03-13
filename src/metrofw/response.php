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

	public function __get($k) {
		return $this->get($k);
	}

	public function __isset($k) {
		return $this->has($k);
	}

	public function set($k, $v) {
		$this->sectionList[$k] = $v;
	}

	public function addTo($k, $v) {
		if (!@is_array($this->sectionList[$k])) {
			if (isset($this->sectionList[$k])) {
				$oldv = $this->sectionList[$k];
				$this->sectionList[$k] = array();
				$this->sectionList[$k][] = $oldv;
			} else {
				$this->sectionList[$k] = array();
			}
		}
		$this->sectionList[$k][] = $v;
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

}
