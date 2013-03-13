<?php

class Metrofw_Output {

	/**
	 * Set the HTTP status first, in case output buffering is not on.
	 */
	public function output($req, $res) {
		$this->statusHeader($res);
		if (isset($res->redir)) {
			$this->redir($res);
			return;
		}

		if ($req->isAjax) {
			header('Content-type: application/json');
			echo json_encode($res->sectionList);
		} else {
			associate_iCanHandle('output', 'metrofw/template.php');
		}
	}

	public function redir($res) {
//		echo 'You will be redirected here: <a href="'.$request->redir.'">'.$request->redir.'</a>';
		header('Location: '.$res->redir);
	}

	/**
	 * Set the HTTP status header again if output buffering is on
	 */
	public function hangup($req) {
		$this->statusHeader($req);
	}

	public function statusHeader($res) {
		switch ($res->get('statusCode')) {
			case 401:
			header('HTTP/1.1 401 Unauthorized');
			break;

			case 404:
			header('HTTP/1.1 404 File Not Found');
			break;

			case 500:
			case 501:
			header('HTTP/1.1 501 Server Error');
			break;

			default:
			header('HTTP/1.1 200 OK');
			break;
		} 
	}
}
