<?php

class Example_Time {

	public function output($req) {
		global $nofw_start;

		$nofw_end = microtime(true);
		echo sprintf("\n<br/>\n%.04f", $nofw_end-$nofw_start);
	}
}
