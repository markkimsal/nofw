<?php

class Metroadmin_Template {

	public function output(&$req, $res) {
		$menu = associate_getMeA('admin_menu');
		$menu->items[] = array('title'=> 'Home',  'href'=>m_appurl(''), 'icon'=>'icon-home');
		if (!$res->unauthorized) {
			associate_iCanHandle('template.adminmenu', 'metroadmin/menu.php');
		}
	}
}
