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
}
