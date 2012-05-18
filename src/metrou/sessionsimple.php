<?php

/**
 * The Simple session object handles "normal" sessions with the $_SESSION 
 * super global and stoes them however your PHP installation would.
 *
 * This session plugin provides consistency in design when you are not using 
 * the DB session.
 */
class Metrou_Sessionsimple extends Metrou_Session {


	public function start() { 
		if (associate_get('session_path', NULL)) {
			session_save_path(associate_get('session_path'));
		}
		parent::start();
		$this->clear('_messages');
		//move saved session messages into regular messages
		if (isset($_SESSION['_sessionMessages']) && is_array($_SESSION['_sessionMessages']) ) {
			foreach ($_SESSION['_sessionMessages'] as $msg) {
				$this->append('_messages',$msg);
			}
		}
		$this->clear('_sessionMessages');
	}

	public function close() { 
		session_write_close();
	}

	public function clear($key) {
		unset($_SESSION[$key]);
	}

	public function set($key, $val) {
		$_SESSION[$key] = $val;
	}

	public function get($key) { 
		if (isset($_SESSION[$key])) {
			return @$_SESSION[$key];
		} else {
			return NULL;
		}
	}

	public function append($key, $val) {
		$_SESSION[$key][] = $val;
	}

	public function setArray($a) {
		foreach ($a as $key=>$val) {
			$_SESSION[$key] = $val;
		}
	}

	/**
	 * Clear all session variables
	 */
	public function clearAll() {
		parent::clearAll();
		foreach ($_SESSION as $k=>$v) {
			unset($_SESSION[$k]);
		}
	}

}
