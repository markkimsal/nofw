<?php

class Nofw_Promise {
	public $thing;
	public $args;

	public function __construct($thing, $args=array()) {
		$this->thing = $thing;
		$this->args  = $args;
	}

	public function __invoke() {
		if (count($this->args)) {
			return call_user_func_array('_getMeA', $args);
		} else {
			return _getMeA($this->thing);
		}
	}

	public function __call($name, $args) {
		return call_user_func_array( array($this(), $name), $args);
	}
}
