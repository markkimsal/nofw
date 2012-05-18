<?php
include_once(dirname(__FILE__).'/session.php');

/**
 * The Db session plugin uses php's session_set_save_handler to 
 * hook in to the session lifecycle and stores session information 
 * in the database.
 */
class Metrou_Sessiondb extends Metrou_Session {

	public $data = array();

	public function start() { 
		session_set_save_handler(
					array(&$this, 'open'),
					array(&$this, 'close'),
					array(&$this, 'read'),
					array(&$this, 'write'),
					array(&$this, 'destroy'),
					array(&$this, 'gc'));
		register_shutdown_function('session_write_close');
		//some php.ini's don't use the gc setting, they assume
		//that a cron will clean up /var/lib/php/
		//We will set a gc func here 10% of the time
		if (rand(1,10) > 9)
			register_shutdown_function( array(&$this, 'gc') );

		parent::start();
	}

	public function destroy($id) {
		if (strlen($id) < 1) { return true; }
		//return false;
		include_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');
		$sess = new Cgn_DataItem('cgn_sess', 'cgn_sess_key');
//		$sess->andWhere('cgn_sess_key',$id);
		$sess->delete($id);
		return true;
	}

	public function gc($maxlifetime=0) {
		$sess = new Cgn_DataItem('cgn_sess');
		$sess->andWhere('saved_on', (time()- $this->timeout), '<');
		$sess->delete();
		return true;
	}


	public function read($id) {
		@include_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');
		$sess = new Cgn_DataItem('cgn_sess');
		$sess->andWhere('cgn_sess_key',$id);
		$sess->_rsltByPkey = false;
		$sessions = $sess->find();
		if (count($sessions)) {
			$sess = $sessions[0];
			if ( strlen($sess->data) ) {
				return (string) $sess->data;
			}
			return '';
		}
		return false;
	}


	public function open($path, $name) {
		return true;
	}

	public function close() {
		$this->commit();
		return true;
	}

	public function write ($id, $sess_data) {
		$sess = new Cgn_DataItem('cgn_sess');
		$sess->andWhere('cgn_sess_key', $id);
		$sess->_rsltByPkey = false;
		$sessions = $sess->find();

		if (count($sessions)) {
			$sess = $sessions[0];
		} else {
			$sess = new Cgn_DataItem('cgn_sess');
			$sess->cgn_sess_key = $id;
		}
		$sess->set('data', $sess_data);
		$sess->set('saved_on', time());
		$sess->save();
		return true;
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
