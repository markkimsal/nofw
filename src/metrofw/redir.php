<?php

class Metrofw_Redir {

	public function output(&$request) {
//		echo 'You will be redirected here: <a href="'.$request->redir.'">'.$request->redir.'</a>';
		header('Location: '.$request->redir);
//		die($request->redir);
	}
}
