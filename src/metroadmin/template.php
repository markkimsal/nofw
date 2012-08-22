<?php

class Metroadmin_Template {

	public function output(&$req) {
		$menu =& associate_getMeA('admin_menu');
		$menu->Home = m_appurl();

		associate_iCanHandle('template.main', 'metroadmin/template.php');
	}

	public function template(&$req, $section='') {

		$html = '';
		$widgetList =& associate_getMeA('widget_list');
		if (empty($widgetList)) return '';
		foreach ($widgetList as $_widget) {
			if ($_widget->isBox) {
				$html .= $this->wrapBox($_widget);
			}
		}

		return $html;
	}

	public function wrapBox($w) {
		$html = '
				<div class="tbox">
				<h2 class="tbox_head red_grad round_top"> </h2>
					<div class="tbox_container">					
					<div class="block box_content round_bottom padding_20">
					'.$w->output;
		$html .= '

					</div>
					</div>
				</div>';
	}
}
