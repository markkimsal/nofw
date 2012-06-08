<?php

class Metrofw_Terrors {

	public function output(&$req) {

		$errors = associate_get('output_errors');
		if (!is_array($errors)) {
			return;
		}
		echo "<ul>\n";
		foreach ($errors as $_er) {
			echo "<li>\n";
			echo $_er;
			echo "</li>\n";
		}
		echo "</ul>\n";
	}

	public function template(&$req, $section) {

		$req = associate_getMeA('request');
		$req->httpStatus = '500';

		$errors = associate_get('output_errors');
		if (!is_array($errors)) {
			return;
		}
		echo "<ul>\n";
		foreach ($errors as $_er) {
			echo "<li>\n";
			echo $_er;
			echo "</li>\n";
		}
		echo "</ul>\n";
	}
}
