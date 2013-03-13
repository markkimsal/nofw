<?php

class Nofw_Proto {

	protected $thing;

	public function __construct($thing) {
		$this->thing = $thing;
	}

	public function __call($name, $args) {
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
