<?php

class Metroadmin_Form_layout {

	public $span = 9;

	public function renderForm($form) {
		$html = '';
 
		$action = '';
		if ($form->action) {
			$action = ' action="'.$form->action.'" ';
		}

		$html .= '<form class="data_form form-horizontal" method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
		if ($form->enctype) {
			$html .= ' enctype="'.$form->enctype.'"';
		}
		$html .= $this->printStyle($form);
		$html .= '>';
		$html .= "\n";

		$_ec = 0;
		foreach ($form->elements as $e) {
			$_ec++;

			$incss = array('forminput');
			if ($e->required) {
				$incss[] = 'form_req';
			}

			$html .= '
				<div class="control-group">
				<label class="control-label" for="'.$e->name.'">'.$e->label.'</label>
				<div class="controls">
			';


			$attr = array('class'=>'span'. ($this->span-3));

			if ($e->type == 'submit') {
				$attr['class'] = "btn btn-primary btn-block";
//				$attr['style'] = "width:220px ";
				//$html .= $e->toHtml($attr, '<i class="icon-white icon-shopping-cart"></i>'.$e->value);
				$html .= $e->toHtml($attr, $e->value);
			}
			else if ($e->type == 'button') {
				$e->size = null;
				$attr['class'] = "btn btn-success btn-block";
				$html .= $e->toHtml($attr, '<i class="icon-shopping-cart icon-white"></i>'.$e->value);
			}
			else {
				$html .= $e->toHtml($attr);
			}

			$html .= '
			</div>
			</div>
			';

		}

		$html .= '
			</div><!-- /control-group -->
			';

		foreach ($form->hidden as $e) {
			$html .= $e->toHtml();
		}

		$html .= '
			</form>
			';

/*
		$trailingHtml = '';
		if ($form->showSubmit || $form->showCancel) {

			$trailingHtml .= '<div class="row">';

			$trailingHtml .= '<div class="span4 offset3">';
				$trailingHtml .= '<div class="form-button-container">'."\n";
			if ($form->showSubmit == TRUE) {
				$trailingHtml .= '<input type="submit" class="containerButtonSubmit btn primary" name="'.$form->name.'_submit" value="'.$form->labelSubmit.'"/>'."\n";
				$trailingHtml .= "\n";
			}
			if ($form->showCancel == TRUE) {
				$trailingHtml .= '<input type="button" class="containerButtonCancel" name="'
					// SCOTTCHANGE
					// .$form->name.'_cancel" onclick="javascript:history.go(-1);" value="'.$form->labelCancel.'"/>';
					.$form->name.'_cancel" onclick="'.$form->actionCancel.'" value="'.$form->labelCancel.'"/>';
				$trailingHtml .= "\n";
			}
			$trailingHtml .= '</div>'."\n";
			$trailingHtml .= '</div>'."\n";
			$trailingHtml .= '</div>'."\n";
		}

*/
		return $html ;
	}

	public function printStyle($form) {
		if ( count ($form->style) < 1) { return ''; }
		$html  = '';
		$html .= ' style="';
		foreach ($form->style as $k=>$v) {
			$html .= "$k:$v;";
		}
		return $html.'" ';
	}
}
