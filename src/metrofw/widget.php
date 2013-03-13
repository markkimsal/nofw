<?php

class Metrofw_Widget {

	public function toHtml() {
		$html = '';
		if (@$this->isBox) {
			//$html .= $this->wrapBox();
			$html .= $this->wrap('box');
		} elseif (@$this->isForm) {
			//$html .= $this->wrapForm();
			$html .= $this->wrap('form');
		} else {
			$html .= $this->wrap('widget');
		}
		return $html;
	}


	public function wrap($wrapper) {
		$request = _getMeA('request');
		$filesep = '/';
		$baseDir = associate_get('template_basedir', 'local/templates/');
		$templateName = associate_get('template_name');
		$fileChoices = array();
		$fileChoices[] = $baseDir.$templateName.$filesep.'views'.$filesep.
				associate_get('view.wrap.'.$wrapper, $wrapper.'.html.php');

		ob_start();
		$success = FALSE;
		foreach ($fileChoices as $_f) {
			if (include($_f)) {
				$success = TRUE;
				break;
			}
		}
		if ($success) {
			return ob_get_contents() . substr( ob_end_clean(), 0, 0);
		} else {
			ob_end_clean();
			return $this->getOutput();
		}
	}



	public function getOutput() {
		if (is_object($this->output)) {
			return $this->output->toHtml();
		} else {
			return $this->output;
		}
	}

/*
	public function wrapBox() {
		$html = '
				<div class="tbox">
				<h2 class="tbox_head red_grad round_top">'.$this->title.'</h2>
					<div class="tbox_container">					
					<div class="block box_content round_bottom padding_20">
					';
		if (is_object($this->output)) {
			$html .= $this->output->toHtml();
		} else {
			$html .= $this->output;
		}
		$html .= '

					</div>
					</div>
				</div>';
		return $html;
	}
*/

/*
	public function wrapForm() {
		$span = $this->span;
		$html = '
				<div class="tbox span'.$span.'">
				<h2 class="tbox_head red_grad round_top">'.$this->title.'</h2>
					<div class="tbox_container">					
					<div class="block box_content round_bottom padding_20">
					';
		if (is_object($this->output)) {
			$layout = $this->output->layout;
			if (is_object($layout)) {
				$layout->span=$span;
			}
			$html .= $this->output->toHtml($layout);
		} else {
			$html .= $this->output;
		}
		$html .= '

					</div>
					</div>
				</div>';
		return $html;
	}
*/
}
