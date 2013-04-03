<?php

class Metrou_User {

	public $username = "anonymous";
	public $password;
	public $email;
	public $locale          = '';
	public $tzone           = '';
	public $userId          = 0;
	public $idProvider      = 'self';
	public $idProviderToken = NULL;
	public $val_tkn         = NULL;
	public $active_on       = 0;

	public $enableAgent = NULL;
	public $agentKey    = NULL;
	 
	// array of group membership groups["public"], groups["admin"], etc.
	public $groups = array();

	public $loggedIn = FALSE;

	//account object
	public $account = NULL;

	//flag for lazy loading
	public $_accountLoaded = FALSE;

	/**
	 * Simple getter
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Return a name suitable for display on a Web site.
	 *
	 * Try to load the user's account.  Combine first and last names if available.
	 * If no account is avaiable, compare the username and emails.  If they are the 
	 * same, return the first half of the email (username@example.com).
	 * If they are different, return the username by itself.
	 *
	 * @return String name for the user suitable for displaying
	 */
	public function getDisplayName() {
		$this->fetchAccount();

		//check the account object
		if ($this->account->firstname != '' ||
			$this->account->lastname != '') {
				return $this->account->firstname. ' '.$this->account->lastname;
		}
		//check if emails are the same as usernames
		if ($this->username === $this->email && strpos($this->username, '@')) {
			return substr($this->username, 0, strpos($this->username, '@'));
		}
		return $this->username;
	}

	/**
	 * Returns true or false based on if the current user is
	 * logged into the site or not
	 */
	public function isAnonymous() {
		return !$this->loggedIn;
	}

	/**
	 * Return one user given the database key
	 *
	 * @return  object  new lcUser
	 * @static
	 */
	public function load($key) {
		if ($key < 1) { return NULL; }

		$item = associate_getMeANew('dataitem', 'cgn_user');
		$item->load($key);
		$user = new Metrou_User();
		$user->setPassword($item->password);
		$user->email  = $item->email;
		$user->locale = $item->locale;
		$user->tzone  = $item->tzone;
		$user->userId = $item->cgn_user_id;
		$user->username = $item->username;
		$user->enableAgent = $item->enable_agent == '1'? TRUE : FALSE;
		if ($user->enableAgent) {
			$user->agentKey    = $item->agent_key;
		}
		return $user;
	}

	/**
	 * Load group association from the database
	 */
	public function loadGroups() {
		$finder = associate_getMeANew('dataitem', 'user_group_rel');
		$finder->andWhere('user_login_id', $this->userId);
		$finder->hasOne('user_group', 'user_group_id');
		$groups = $finder->find();
		$this->groups = array();
		foreach ($groups as $_group) {
			if ($_group->code != '')
			$this->groups[ $_group->user_group_id ] = $_group->code;
		}
	}

	/**
	 * Return an array of cgn_group_id integers
	 *
	 * @return 	Array 	list of primary keys of groups this user belongs to
	 */
	public function getGroupIds() {
		if (count($this->groups)) {
			return array_keys($this->groups);
		} else {
			return array(0);
		}
	}

	/**
	 * Add a user to a group
	 *
	 * @param int $gid 		internal database id of the group
	 * @param string $gcode 		special code for the group
	 */
	public function addToGroup($gid, $gcode) {
		$this->groups[(int)$gid] = $gcode;
	}


	/**
	 * Remove a user to a group
	 *
	 * @param int $gid 		internal database id of the group
	 * @param string $gcode 		special code for the group
	 */
	public function removeFromGroup($gid, $gcode) {
		unset($this->groups[$gid]);
	}

	/**
	 * Write groups to the database and the session.
	 *
	 * If this user has a session, update it as well.
	 */
	public function saveGroups() {
		$finder = associate_getMeANew('dataitem', 'cgn_user_group_link');
		$finder->andWhere('cgn_user_id', $this->getUserId());
		$items = $finder->find();
		$oldGids = array();
		if (is_array($items))foreach ($items as $_item) {
			$oldGids[] = $_item->cgn_group_id;
		}
		$newGids = $this->getGroupIds();
		$delGids = array_diff($oldGids, $newGids);
		$addGids = array_diff($newGids, $oldGids);

		foreach ($addGids as $_g) {
			if ($_g == 0) { continue; }
			$newGroup = associate_getMeANew('dataitem', 'cgn_user_group_link');
			//table doesn't have a primary key
			unset($newGroup->cgn_user_group_link_id);
			$newGroup->cgn_group_id = $_g;
			$newGroup->cgn_user_id = $this->getUserId();
			$newGroup->active_on = time();
			$newGroup->save();
		}

		foreach ($delGids as $_g) {
			$oldGroup = associate_getMeANew('dataitem', 'cgn_user_group_link');
			$oldGroup->andWhere('cgn_group_id', $_g);
			$oldGroup->andWhere('cgn_user_id', $this->getUserId());
			$oldGroup->delete();
		}

		$this->updateSessionGroups();
	}

	/**
	 * If this user is the logged in user of the session, save the groups 
	 * to the session.
	 */
	public function updateSessionGroups() {
		$mySession = associate_getMeA('session');
		if ($this->getUserId() == $mySession->get('userId')) {
			$mySession->set('groups', serialize( $this->groups ));
		}
	}

	/**
	 * Load the account object if it is not already loaded.
	 *
	 * The account object shall be a simple Metrodb_Dataitem.
	 */
	public function fetchAccount() {
		if ($this->_accountLoaded) {
			return;
		}
		$this->account  = associate_getMeANew('dataitem', 'cgn_account');
		$this->account->andWhere('cgn_user_id', $this->userId);
		$this->account->load();

		$this->_accountLoaded = TRUE;
	}

	/**
	 * Turn on the API agent feature.
	 *
	 * If $createKey is true make a new key only if none exists
	 */
	public function enableApi($createKey = FALSE) {
		$this->enableAgent = TRUE;

		//peek directly at the db, because we don't keep the 
		// agent key loaded in memory normally
		$d = associate_getMeANew('dataitem', 'cgn_user');
		$d->load( $this->getUserId());

		if ($d->agent_key == '') {
			if(!$this->regenerateAgentKey()) {
				//failed, turn off agent api
				$this->enableAgent = FALSE;
			}
		}
		$this->save();
		return $this->enableAgent;
	}


	/**
	 * Turn on the API agent feature.
	 *
	 * If $createKey is true make a new key only if none exists
	 */
	public function disableApi() {
		$this->enableAgent = FALSE;
		$this->save();
		return $this->enableAgent === FALSE;
	}



	/**
	 * Create a new, unique agent key string
	 */
	public function regenerateAgentKey($deep=0) {
		if ($deep == 3) {
			$this->agentKey = '';
			return FALSE;
		} 
		$rand = rand(100000000, PHP_INT_MAX);
		$crc = sprintf('%u',crc32($rand));
		$tok =  base_convert( $rand.'a'.$crc, 11,26);

		$d = associate_getMeANew('dataitem', 'cgn_user');
		$d->andWhere('agent_key', $tok);
		$t = $d->find();
		if (is_array($t) && count($t) > 0) {
			$this->regenerateAgentKey($deep+1);
		} else {
			$this->agentKey = $tok;
		}
		return TRUE;
	}

	/**
	 * Returns true or false if this user is in a group
	 */
	public function belongsToGroup($g) {
		if (!is_array($this->groups) ) { return false;} 
		return in_array($g,$this->groups);
	}


	public function getUserId() {
		return @$this->userId;
	}

	/**
	 * @static
	 */
	/*
	static function registerUser($u, $idProvider='self') {
		//check to see if this user exists
		$finder = associate_getMeANew('dataitem', 'cgn_user');
		$finder->andWhere('id_provider', $idProvider);
		if ($u->idProviderToken !== NULL) {
			$finder->andWhere('id_provider_token', $u->idProviderToken);
		}

		$finder->andWhere('email', $u->email);
		if ($u->username == '') {
			$finder->orWhereSub('username', $u->email);
		} else {
			$finder->orWhereSub('username', $u->username);
		}
		$finder->_rsltByPkey = FALSE;
		$results = $finder->find();

		if (count($results)) {
			$foundUser = $results[0];
			if (!$foundUser->_isNew && 
				($foundUser->username == $u->username ||
				$foundUser->email == $u->email ||
				$foundUser->username == $u->email)) {
				//username exists
				return false;
			}
		}

		//save
		$u->idProvider = $idProvider;
		$x = $u->save();
		//if there is a duplicate key error, it is a PHP error.

		if( $u->userId > 0 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	 */

	public function save() {
		$user = associate_getMeANew('dataitem', 'cgn_user');
		$user->_pkey = 'cgn_user_id';
		$user->load($this->userId);
		$user->email    = $this->email;
		$user->locale   = $this->locale;
		$user->tzone    = $this->tzone;
		$user->username = $this->username;
		$user->password = $this->password;
		$user->val_tkn  = $this->val_tkn;
		$user->_nuls[]  = 'val_tkn';
		$user->_nuls[]  = 'id_provider_token';

		if (!$this->userId) {
			$this->_prepareRegInfo($user);
		}

		//only if there's been a change
		if ($this->agentKey !== NULL) {
			$user->agent_key = $this->agentKey;
		}
		//only if there's been a change
		if ($this->enableAgent !== NULL) {
			$user->enable_agent = $this->enableAgent? 1 : 0;
		}

		$result = $user->save();

		if ($result !== FALSE) {
			$this->userId = $result;
		}
		return $result;
	}

	/**
	 * Save some user session data into the $dataItem
	 *
	 * This method does not set reg_cpm, that is left up to user scripts.
	 * @param Object $dataItem  Metrodb_Dataitem class from cgn_user table
	 */
	protected function _prepareRegInfo($dataItem) {
		$mySession = associate_getMeA('session');
		$dataItem->_nuls[] = 'reg_cpm';
		$dataItem->_nuls[] = 'reg_id_addr';
		$dataItem->_nuls[] = 'id_provider_token';
		$dataItem->set('reg_date', time());
		$dataItem->set('login_date', time());

		if ($mySession->get('_sess_referrer') != NULL ) {
			$dataItem->set('reg_referrer', $mySession->get('_sess_referrer'));
			$dataItem->set('login_referrer', $mySession->get('_sess_referrer'));
		}
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$dataItem->set('reg_ip_addr', $_SERVER['REMOTE_ADDR']);
			$dataItem->set('login_ip_addr', $_SERVER['REMOTE_ADDR']);
		}

		$dataItem->set('active_on', $this->active_on);

		//handle ID Providers
		$dataItem->set('id_provider', $this->idProvider);
		$dataItem->set('id_provider_token', $this->idProviderToken);
	}

	/**
	 * Save login info back to the user table
	 *
	 * This method does not set reg_cpm, that is left up to user scripts.
	 * @param Object $dataItem  Metrou_Dataitem class from cgn_user table
	 */
	protected function _recordLogin() {
		if (!$this->userId) {
			return;
		}
		$dataItem = associate_getMeANew('dataitem', 'cgn_user');
		$dataItem->set('login_date', time());
		$mySession = associate_getMeA('session');
		if ($mySession->get('_sess_referrer') != NULL ) {
			$dataItem->set('login_referrer', $mySession->get('_sess_referrer'));
		}
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$dataItem->set('login_ip_addr', $_SERVER['REMOTE_ADDR']);
		}
		$dataItem->_isNew = FALSE;
		$dataItem->set('cgn_user_id', $this->userId);
		$dataItem->save();
	}

	/**
	 * Grab the current session and apply values to the current user object.
	 *
	 * This is to avoid a database hit for most commonly accessed user 
	 * properties.
	 */
	public function startSession() {
		$mySession = associate_getMeA('session');
		if ($mySession->get('userId') != 0 ) {
			$this->userId   = $mySession->get('userId');
			$this->username = $mySession->get('username');
			$this->email    = $mySession->get('email');
			$this->password = $mySession->get('password');
			$this->locale   = $mySession->get('locale');
			$this->tzone    = $mySession->get('tzone');
			$this->active_on = $mySession->get('active_on');
			$this->enableAgent = $mySession->get('enableAgent');
			if ($this->enableAgent) {
				$this->agentKey = $mySession->get('agentKey');
			}

			$this->loggedIn = true;
			$this->groups = unserialize($mySession->get('groups'));

			if ($this->tzone != '' && function_exists('date_default_timezone_set')) {
				@date_default_timezone_set($this->tzone);
			}
		}
	}

	/**
	 * links an already started session with a registered user
	 * sessions can exist w/anonymous users, this function
	 * will link userdata to the open session;
	 * also destroys multiple logins
	 */
	public function bindSession() {
		$mySession = associate_getMeA('session');
		$mySession->setAuthTime();
		$mySession->set('userId',$this->userId);
		$mySession->set('lastBindTime',time());
		$mySession->set('username',$this->username);
		$mySession->set('email',$this->email);
		$mySession->set('password',$this->password);
		$mySession->set('locale',$this->locale);
		$mySession->set('tzone',$this->tzone);
		$mySession->set('active_on', $this->active_on);
		$mySession->set('enableAgent',$this->enableAgent);
		if ($this->enableAgent) {
			$mySession->set('agentKey',$this->agentKey);
		}
		$mySession->set('groups',serialize( $this->groups ));
		$this->loggedIn = true;

		if ($this->tzone != '' && function_exists('date_default_timezone_set')) {
			@date_default_timezone_set($this->tzone);
		}
	}

	/**
	 * Erases the link between a logged in user ID and the session, 
	 * but keeps the data for debugging/logging.
	 */
	public function unBindSession() {
		$mySession = associate_getMeA('session');

		$mySession->clear('userId');
		$mySession->clear('lastBindTime');
		$mySession->clear('username');
		$mySession->clear('email');
		$mySession->clear('password');
		$mySession->clear('groups');
		$mySession->clear('locale');
		$mySession->clear('tzone');
		$mySession->clear('enableAgent');
		if ($this->enableAgent) {
			$mySession->clear('agentKey');
		}
		$this->loggedIn = false;
	}


	/**
	 * Erases the users current session.
	 * if you simply want to end a session, but keep the
	 * data in the db for records, use $u->unBindSession();
	 */
	public function endSession() {
		$mySession = associate_getMeA('session');
		$mySession->erase();
	}

	public function sayGoodbye() {
		return "Goodbye, All!";
	}

	public function afterLogin() {
		$this->loggedIn = TRUE;
		$this->loadGroups();
		$this->bindSession();
		$this->_recordLogin();
	}
}
