<?php
class Metroadmin_Authenticate {

	/**
	 * Checks if user is in group 'admin' or group 'super'
	 */
	public function authenticate($req, $res) {
		$remember = $req->cleanInt('remember');
		if ($remember) 
			$remember = 1;

		$user = associate_getMeA('user');
		//session expires 7 days later if they 
		// checked 'remember'
		$exp = $remember * 60 * 60 * 24 * 7;
		$user->startSession($exp);

		if ($user->belongsToGroup('admin') || $user->belongsToGroup('super')) {
			return TRUE;
		}

		//nothing gets to process if the user is not authenticated
		associate_iCanOwn('process', 'metroadmin/authenticate.php');
		$res->unauthorized = TRUE;
		$res->set('statusCode', 401);

		//if user is not logged in, let them authenticate
		if ($user->isAnonymous()) {
			associate_iCanHandle('output', 'metroadmin/authenticate.php', 1);
		} else {
			$res->addTo('items', 'Your user do not have access to this page.');
			$req->sparkMsg[] = 'Access denied';
		}
	}

	/**
	 * Add a form to repsonse->items
	 * Only show when we have no permission
	 * 
	 */
	public function output($req, $res) {

		$f = associate_getMeA('form');
		$f->action = m_appurl('dologin', '', 1);

		$ele = new Metroform_ElementInput('username', 'E-mail');
		$f->appendElement($ele, @$values['username']);

		$ele = new Metroform_ElementPassword('password', 'Password');
		$f->appendElement($ele, @$values['password']);

		$ele = new Metroform_ElementCheck('remember', '');
		$ele->addChoice('Remember Me', 0);
		$f->appendElement($ele);

		$ele = new Metroform_ElementSubmit('login', '');
		$f->appendElement($ele, 'Login');

		associate_iAmA('form.layout', 'metroadmin/form_layout.php');

		$f->layout = associate_getMeA('form.layout');
		$f->layout->span=6;
		$widget =& associate_getMeA('widget');
		$widget->output = $f;
		$widget->isForm = true;
		$widget->span = 6;
		$widget->title = 'Login';
		$res->addTo('items', $widget);
	}
}
