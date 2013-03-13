<?php
class Metroadmin_Menu {

	public function template(&$req, $section) {
		$html ='';
		foreach (associate_getMeA('admin_menu')->items as $_item) {

			$html .= '
            <ul class="sidebar-nav">
			<li class="light red_grad"><a href="'.$_item['href'].'">
				<div class="icon">
				<i class="'.$_item['icon'].' icon-white"></i></div>'.$_item['title'].'</a></li>
			</ul>
			';
		}
		return $html;
	}
}

