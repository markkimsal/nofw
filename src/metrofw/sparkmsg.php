<?php

class Metrofw_Sparkmsg {

	public function template(&$request) {
		if (!isset($request->sparkMsg) ) return;

		$ret = '';
		foreach ($request->sparkMsg as $_spk) {
			$ret .= $_spk;
		}
		return $ret;
	}
}
