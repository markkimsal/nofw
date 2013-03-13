<?php
/**
 * Metrou_Authenticator
 *
 */
class Metrou_Authenticator {

	public $handler  = NULL;
	public $ctx      = NULL;
	public $subject  = NULL;
	public $configs  = array();

	/**
	 * Initialize a new handler for the given context.
	 * If no context is supplied, a default handler will be created.
	 * The default handler is based on the local mysql installation.
	 */
	public function authenticate(&$request, $response) {
		$configs       = associate_get('auth_configs', array());
		$this->handler = associate_getMeA('auth_handler');
		if ($this->handler == NULL || $this->handler instanceof Nofw_Proto) {
			$this->handler = new Metrou_Authdefault();
		}
		$this->handler->initContext($configs);

		$user = associate_getMeA('user');
		$uname = $request->cleanString('username');
		$pass  = $request->cleanString('password');

		$this->subject = Metrou_Subject::createFromUsername($uname, $pass);
		$err = $this->handler->authenticate($this->subject);

		if ($err) {
			$request->sparkMsg[] = 'Login Failed';
			return FALSE;
		}
		$request->sparkMsg[] = 'Login Succeeded';
		if ($request->appUrl == 'login') {
			$response->redir = m_appurl();
		}

		@$this->handler->applyAttributes($this->subject, $user);

		$user->username = $uname;
		$user->password = $this->handler->hashPassword($pass);

		$user->afterLogin();

		//doesn't do anything
		return TRUE;
	}
}

interface Metrou_Authiface { 

	/**
	 * Must return a reference to this
	 *
	 * @return Object Metrou_Authiface
	 */
	public function initContext($ctx);

	/**
	 * Return any positive number other than 0 to indicate an error
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function authenticate($subject);

	/**
	 * Save a connection to this user in the local user database.
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function applyAttributes($subject, $existingUser);

}

class Metrou_Authdefault implements Metrou_Authiface {

	public function initContext($ctx) {
		return $this;
	}


	/**
	 * Return any positive number other than 0 to indicate an error
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function authenticate($subject) {

		if (!isset($subject->credentials['passwordhash'])) {
			$subject->credentials['passwordhash'] = $this->hashPassword($subject->credentials['password']);
		}

		$finder = associate_getMeANew('dataitem', 'user_login');
		$finder->andWhere('username', $subject->credentials['username']);
		$finder->andWhere('password', $subject->credentials['passwordhash']);
		$finder->_rsltByPkey = FALSE;
		$results = $finder->findAsArray();

		if (!count($results)) {
			return 501;
		}

		if( count($results) !== 1) {
			//too many results, account is not unique
			return 502;
		}

		$subject->attributes = array_merge($subject->attributes, $results[0]);
		return 0;
	}

	public function hashPassword($p) {
		return md5(sha1($p));
	}

	/**
	 * Save a connection to this user in the local user database.
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function applyAttributes($subject, $user) { 
		$attribs        = $subject->attributes;
		$user->email    = $attribs['email'];
		$user->locale   = $attribs['locale'];
		$user->tzone    = $attribs['tzone'];
		$user->userId   = $attribs['user_login_id'];

		$user->enableAgent = $attribs['enable_agent'] == '1'? TRUE : FALSE;
		if ($user->enableAgent) {
			$user->agentKey    = $attribs['agent_key'];
		}
		return 0; 
	}
}

class Metrou_Authldap implements Metrou_Authiface {

	public $dsn        = '';
	public $bindBaseDn = '';
	protected $ldap    = NULL;

	public function initContext($ctx) {
		$this->dsn        = $ctx['dsn'];
		$this->bindBaseDn = $ctx['bindDn'];
		$this->authDn     = $ctx['authDn'];
		return $this;
	}

	public function setLdapConn($l) {
		$this->ldap = $l;
	}

	public function getLdapConn() {
		if ($this->ldap === NULL) {
			$this->ldap = _getMeA('ldapconn', $this->dsn);
		}
		return $this->ldap;
	}

	/**
	 * Return any positive number other than 0 to indicate an error
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function authenticate($subject) {

		if (!isset($subject->credentials['passwordhash'])) {
			$subject->credentials['passwordhash'] = $this->hashPassword($subject->credentials['password']);
		}

		$rdn = sprintf($this->bindBaseDn, $subject->credentials['username']);
		$ldap = $this->getLdapConn();

//		$ldap->setBindUser($rdn, $subject->credentials['password']);
		$result = $ldap->bind();

		$basedn = $this->authDn;
		//query for attributes
		$res = $ldap->search($basedn, '(userid='.$subject->credentials['username'].')', array('entryUUID', 'mail', 'tzone', 'locale', 'dn', 'entryDN'));

		if ($res === FALSE) {
			//search failed
			$ldap->unbind();
			return 501;
		}

		$ldap->nextEntry();
		$attr = $ldap->getAttributes();
		$ldap->unbind();
		foreach ($attr as $_attr => $_valList) {
			if ($_attr == 'mail')
				$subject->attributes['email'] = $_valList[0];

			if ($_attr == 'entryDN')
				$subject->attributes['dn'] = $_valList[0];
		}

//		$subject->attributes = array_merge($subject->attributes, $results[0]);
		return 0;
	}

	public function hashPassword($p) {
		return md5(sha1($p));
	}

	/**
	 * Save a connection to this user in the local user database.
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function applyAttributes($subject, $existingUser) {
		$existingUser->username = $subject->credentials['username'];
		$existingUser->password = $subject->credentials['passwordhash'];

		$existingUser->idProviderToken = $subject->attributes['dn'];
		Cgn_User::registerUser($existingUser, 'ldap');
		//tell the subject that what its new ID is
		$subject->attributes['user_login_id'] = $existingUser->userId;
		return 0;
	}
}

class Metrou_Subject {

	public $credentials = array();
	public $attributes  = array();
	public $domain      = '';
	public $domainId    = 0;

	public static function createFromUserName($uname, $pass) {

		$subj = new Metrou_Subject();
		$subj->credentials['username'] = $uname;
		$subj->credentials['password']   = $pass;
		return $subj;
	}
}

