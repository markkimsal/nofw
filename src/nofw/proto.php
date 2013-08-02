<?php

class Nofw_Proto {

	protected $thing;

	public function __construct($thing) {
		$this->thing = $thing;
	}

	/**
	 * Intercept all function calls so there are no stopping errors.
	 * in DEV mode (associate_set('env', 'dev')) a trace will be emitted.
	 */
	public function __call($name, $args) {
		//only show proto messages in dev mode
		if (associate_get('env') != 'dev') {
			return;
		}
		$bt = debug_backtrace();
		$line = $bt[0]['line'];
		$file = $bt[0]['file'];
		$bt = null;
		$parts = explode(DIRECTORY_SEPARATOR, $file);
		$fname = array_pop($parts);
		$file = array_pop($parts).DIRECTORY_SEPARATOR.$fname;
		var_dump("Called: ".$name." against proto object of type: ".$this->thing." from: ".$file." (".$line.").");
		return $this;
	}

	public function __toString() {
		return "Proto object of type: ".$this->thing.PHP_EOL;
	}
}
