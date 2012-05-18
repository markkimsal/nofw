<?php

class Metrou_Session {

	public $sessionId = '';
	public $started = FALSE;
	public $sessionName = 'METROSESSION';
	public $timeout          = 88200;  //24.5 hour timeout
	public $inactivityReAuth = 600;   //10 min timeout for auth
	public $authTime = -1;
	public $touchTime = -1;
	public $lastTouchTime = -1;

	/**
	 * Start a session and save and HTTP Referer
	 */
	public function start() {
		session_name($this->sessionName);
		if ($this->started) trigger_error('double session');
			$this->started = TRUE;
		ini_set('session.gc_maxlifetime', $this->timeout);
		@session_start();
		$this->sessionId = session_id();
		$this->touch();
	}

	public function close() { 
		$this->started = FALSE;
	}

	public function set($key, $val) { }

	public function setArray($a) { }

	public function get($key) { }

	public function append($key, $val) { }
	
	public function getSessionId() { 
		return $this->sessionId;
	}

	public function isSessionStale() { }

	/**
	 * Set a usage time stamp for this session.
	 */
	public function touch($t=0) {
		if ($this->touchTime === -1) {
			$this->begin();
		}

		if ($this->touchTime !== -1) {
			$this->lastTouchTime = $this->touchTime;
		}

		if ($t == 0) {
			$this->touchTime = time();
		} else {
			$this->touchTime = $t;
		}
		$this->set('_touch', $this->touchTime);
		$this->set('_lastTouch', $this->lastTouchTime);
	}

	/**
	 * Set the last time a session was authorized, as in
	 * a user submitting a password
	 */
	public function setAuthTime($t=0) { 
		if ($t == 0) {
			$this->authTime = time();
		} else {
			$this->authTime = $t;
		}

		$this->set('_auth', $this->authTime);
	}

	/**
	 * Return true if the activity has been too long and
	 * the system wants re-authorization.  The session is still
	 * active, but this function recommends asking for a new
	 * password for more security.
	 */
	public function needsReAuth() { 
		$lastTouch = $this->lastTouchTime;
		if ($lastTouch === -1) {
			$lastTouch = $this->touchTime;
		}
		if ( time() - $lastTouch >= $this->inactivityReAuth ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Flush needed variables to the session storage.
	 * For DB based sessions these values might need to go
	 * into the table and not the session itself.
	 */
	public function commit() { 
		$this->set('_auth', $this->authTime);
		$this->set('_touch', $this->touchTime);
		$this->set('_lastTouch', $this->lastTouchTime);
	}

	/**
	 * This function pulls special variables out of the session storage.
	 * Record any HTTP Referer
	 *
	 * Opposite of commit, like magic __wakeup.
	 */
	public function begin() {
		$touch = $this->get('_touch');
		if ($touch !== NULL) {
			$this->touchTime = $touch;
		}
		$auth = $this->get('_auth');
		if ($auth !== NULL) {
			$this->authTime = $auth;
		}
		$last = $this->get('_lastTouch');
		if ($last !== NULL) {
			$this->lastTouchTime = $last;
		}

		//if this is the first time 
		//save any HTTP Referer for logging
		if ($this->touchTime === -1 && isset($_SERVER['HTTP_REFERER'])) {
			$this->set('_sess_referrer', $_SERVER['HTTP_REFERER']);
		}
	}

	/**
	 * Completely erase a session
	 */
	public function erase() {
		$this->clearAll();
		session_destroy();
		//setcookie($this->sessionName, '');
		$this->started = FALSE;
	}

	/**
	 * Clear all session variables
	 */
	public function clearAll() {
		$this->authTime = 0;
		$this->touchTime = 0;
		$this->lastTouchTime = 0;
	}

	/**
	 * If serialized, we won't call the constructor again
	 */
	public function __wakeup() {
		$this->started = FALSE;
	}

	public function isNew() {
		return $this->lastTouchTime === -1;
	}
}

