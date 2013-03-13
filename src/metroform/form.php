<?php


class Metroform_Form {

	public $name      = 'nofw_form';
	public $elements  = array();
	public $hidden    = array();
	public $helpList  = array();
	public $hintList  = array();
	public $label     = '';
	public $action;
	public $method;
	public $enctype;
	public $layout     = NULL;           //layout object to render the form
	public $width      = '';
	public $style      = array();
	public $showSubmit = TRUE;
	public $labelSubmit = 'Save';
	public $showCancel = TRUE;
	public $labelCancel = 'Cancel';
	public $actionCancel = 'javascript:history.go(-1);';
	public $showLabel   = TRUE;

	public $subFormList = array();



	public $formHeader = '';
	public $formFooter = '';

	public function __construct($name = 'cgn_form', $action='', $method='POST', $enctype='') {
		$this->name = $name;
		$this->action = $action;
		$this->method = $method;
		$this->enctype = $enctype;
	}

	public function appendElement($e, $value='', $hintValue = '', $details='') {
		if ($value !== '') {
			$e->setValue($value);
//			$e->value = $value;
		}
		if ($e->type == 'hidden') {
			$this->hidden[] = $e;
		} else {
			$this->elements[] = $e;
			$this->helpList[] = $details;
			$this->hintList[] = $hintValue;
		}
	}

	/**
	 * Displays fields of another form as a new fieldset
	 */
	public function addSubForm($subForm) {
		$this->subFormList[] = $subForm;
	}

	/**
	 * Combine this element one the same row as the previous one
	 */
	public function stackElement($e, $value='') {
		if ($value !== '') {
			$e->setValue($value);
		}
		if ($e->type == 'hidden') {
			$elemList = $this->hidden;
		} else {
			$elemList = $this->elements;
		}

		$top = count($elemList);
		$last = $this->elements[$top-1];
		if (strtolower(get_class($last)) == 'cgn_form_element_bag') {
			$last->stackElement($e);
			$elemList[$top-1] = $last;
		} else {
			$bag = new Metroform_Element_Bag();
			$bag->stackElement($last);
			$bag->stackElement($e);
			$elemList[$top-1] = $bag;
		}

		if ($e->type == 'hidden') {
			$this->hidden = $elemList;
		} else {
			$this->elements = $elemList;
		}
	}


	public function toHtml($layout=NULL) {
		if ($layout !== NULL && $layout instanceof Metroform_Layout) {
			return $layout->renderForm($this);
		}
		if ($this->layout !== NULL) {
			return $this->layout->renderForm($this);
		}
		$layout = new Metroform_Layout();
		return $layout->renderForm($this);
	}

	public function setShowSubmit($show=TRUE,$labelSubmit='Save') {
		$this->showSubmit = $show;
		$this->labelSubmit = $labelSubmit;
	}

	public function setShowCancel($show=TRUE,$labelCancel='Cancel',$actionCancel='javascript:history.go(-1);') {
		$this->showCancel = $show;
		$this->labelCancel = $labelCancel;
		$this->actionCancel = $actionCancel;
	}

	public function setShowLabel($showLabel=true) {
		$this->showLabel = $showLabel;
	}

	public function setShowTitle($showTitle=true) {
		$this->setShowLabel($showTitle);
	}

	/**
	 * Check that each required field is filled in
	 *
	 * @return Bool True if all required inputs have values, false otherwise
	 */
	public function validate($values) {
		$validated = TRUE;
		foreach ($this->elements as $_k => $_v) {
			if ($_v->required) {
				if ( (!isset($values[$_v->name])) || $values[$_v->name] == '' ) {
					$validated = false;
					$this->validationErrors[$_v->name][] = 601;
				} else {
					if (!$_v->validate($values[$_v->name])) {
						$validated = false;
						$this->validationErrors[$_v->name][] = 601;
					}
				}
			}
		}
		return $validated;
	}
}

class Metroform_Element {

	public $type;
	public $name;
	public $id;
	public $label;
	public $hint;
	public $value;
	public $size;
	public $jsOnChange = '';
	public $required   = false;

	public function __construct($name, $label=-1, $size=30) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
		$this->size = $size;
	}

	/**
	 * Set the value for this element
	 */
	public function setValue($v) {
		$this->value = $v;
	}

	public function toHtml($attr=array()) {
		if ($this->size) {
			$attr['size'] = $this->size;
		}
		if ($this->hint) {
			$attr['placeholder'] = $this->hint;
		}

		$xtra = '';
		foreach ($attr as $_k => $_v) {
			$xtra .= $_k.'="'.$_v.'" ';
		}
		return '<input type="'.$this->type.'" name="'.$this->name.'" id="'.$this->name.'" '.$xtra.' value="'.htmlentities($this->value,ENT_QUOTES).'" />';
	}

	/**
	 * Add custom javascript for the onchange event.
	 */
	public function setJsOnChange($js) {
		$this->jsOnChange = $js;
	}

	/**
	 * Get custom javascript for the onchange event.
	 */
	public function getJsOnChange() {
		return $this->jsOnChange;
	}

	/**
	 * Return true if this element is required and the value is not empty.
	 */
	public function validate($value) {
		if ($this->required) {
			if ( empty($value) ) {
				return false;
			}
		}
		return true;
	}

	public function setHint($h) {
		$this->hint = $h;
	}
}


class Metroform_Element_Bag extends Metroform_Element {
	public $elemList = array();
	public $type     = 'aggregate';

	public function __construct() {
	}

	/**
	 * Use the first element's label, name and size as this element's label, name and size
	 */
	public function stackElement($el) {
		if (!count($this->elemList)) {
			$this->label = $el->label;
			$this->name = $el->name;
			$this->size = $el->size;
		}

		$this->elemList[] = $el;
	}

	/**
	 * Return one html string representing both inputs
	 */
	public function toHtml($attr=array()) {
		$html = '';
		foreach ($this->elemList as $_el) {
			$html .= $_el->toHtml($attr);
		}
		return $html;
	}
}

class Metroform_ElementLabel extends Metroform_Element {
	public $type  = 'label';

	public function __construct($name, $label=-1,  $value= '') {
			$this->name = $name;
			$this->value = $value;
			$this->label = $label;
	}

	public function toHtml($attr=array()) {
		return '<span name="'.$this->name.'" id="'.$this->name.'">'.htmlentities($this->value,ENT_QUOTES).'</span>';
	}
}

class Metroform_ElementContentLine extends Metroform_Element {
	public $type = 'contentLine';

	public function __construct($value= '') {
			$this->value = $value;
	}

	public function toHtml($attr=array()) {
		return $this->value;
	}
}

class Metroform_ElementHidden extends Metroform_Element {
	public $type = 'hidden';
}


class Metroform_ElementInput extends Metroform_Element {
	public $type = 'text';
}

class Metroform_ElementFile extends Metroform_Element {
	public $type = 'file';
}

class Metroform_ElementButton extends Metroform_Element {
	public $type = 'button';
	public $href;

	public function toHtml($attr=array(), $inner='') {
		if ($this->size) {
			$attr['size'] = $this->size;
		}
		if ($this->hint) {
			$attr['placeholder'] = $this->hint;
		}

		$input = 'button';
		if ($this->href) {
			$input = 'a';
			$attr['href'] = $this->href;
		}

		$xtra = '';
		foreach ($attr as $_k => $_v) {
			$xtra .= $_k.'="'.$_v.'" ';
		}
		return '<'.$input.' type="'.$this->type.'" name="'.$this->name.'" id="'.$this->name.'" '.$xtra.' value="'.htmlentities($this->value,ENT_QUOTES).'">'.$inner.'</'.$input.'>';
	}
}

class Metroform_ElementSubmit extends Metroform_ElementButton {
	public $type = 'submit';
}


class Metroform_ElementText extends Metroform_Element {
	public $type = 'textarea';
	public $rows;
	public $cols;

	public function __construct($name, $label=-1,$rows=15,$cols=85) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
		$this->rows = $rows;
		$this->cols = $cols;
	}


	public function toHtml($attr=array()) {
		$xtra = '';
		foreach ($attr as $_k => $_v) {
			$xtra .= $_k.'="'.$_v.'" ';
		}

		$html  = '';
		$html .= '<textarea name="'.$this->name.'" id="'.$this->name.'" rows="'.$this->rows.'" cols="'.$this->cols.'" '.$xtra.'>'.htmlentities($this->value,ENT_QUOTES).'</textarea>'."\n";
		return $html;
	}
}


class Metroform_ElementPassword extends Metroform_Element {
	public $type = 'password';
}


class Metroform_ElementRadio extends Metroform_Element {
	public $type = 'radio';
	public $choices = array();

	public function addChoice($c, $v='', $selected=0) {
		$top = count($this->choices);
		$this->choices[$top]['title'] = $c;
		$this->choices[$top]['selected'] = $selected;
		$this->choices[$top]['value'] = $v;
		return count($this->choices)-1;
	}

	/**
	 * Sets the selected choices index
	 */
	public function setValue($v) {
		foreach ($this->choices as $idx=>$c) {
			if ($c['value'] === $v) {
				$this->choices[$idx]['selected'] = true;
				break;
			}
		}
	}

	public function toHtml($attr=array()) {
		$html = '';
		$html .= '<ul class="inputs-list">';
		foreach ($this->choices as $cid => $c) {
			$selected = '';
			if ($c['value'] === '') {
				$value = sprintf('%02d', $cid+1);
			} else {
				$value = $c['value'];
			}
			if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
		$html .= '<li><input type="radio" name="'.$this->name.'" id="'.$this->name.sprintf('%02d',$cid+1).'" value="'.$value.'"'.$selected.'/><label class="label-radio" for="'.$this->name.sprintf('%02d',$cid+1).'">'.$c['title'].'</label></li> ';
		}
		$html .= '</ul>';
		return $html;
	}

	public function validate($value) {
		foreach ($this->choices as $_k => $_v) {
			if ($_v['value'] == $value) {
				return true;
			}
		}
		return false;
	}
}

class Metroform_ElementSelect extends Metroform_Element {
	public $type = 'select';
	public $choices = array();
	public $size = 1;
	public $selectedVal = NULL;

	public function __construct($name, $label=-1, $size=7, $selectedVal = NULL) {
		parent::__construct($name, $label, $size);
		$this->selectedVal = $selectedVal;
	}

	public function addChoice($c, $v='', $selected=0) {
		$top = count($this->choices);

		if ($this->selectedVal == $v) {
			$selected = true;
		}

		$this->choices[$top]['title'] = $c;
		$this->choices[$top]['selected'] = $selected;
		$this->choices[$top]['value'] = $v;

		return count($this->choices)-1;
	}

	/**
	 * Sets the selected choices index
	 */
	public function setValue($v) {
		foreach ($this->choices as $idx=>$c) {
			if ($c['value'] === $v) {
				$this->choices[$idx]['selected'] = true;
				break;
			}
		}
	}

	public function toHtml($attr=array()) {
		$onchange = '';
		if ($this->jsOnChange !== '') {
			$onchange = ' onchange="'.$this->jsOnChange.'" ';
		}
		$xtra = '';
		foreach ($attr as $_k => $_v) {
			$xtra .= $_k.'="'.$_v.'" ';
		}

		$html = '<select name="'.$this->name.'" id="'.$this->name.'" size="'.$this->size.'" '.$onchange.' '.$xtra.'>';
		foreach ($this->choices as $cid => $c) {
			$selected = '';
			if ($c['selected'] == 1) { $selected = ' SELECTED="SELECTED" '; }
			if ($c['value'] != '') { $value = ' value="'.htmlentities($c['value']).'" ';} else { $value = ' value="" '; }
		$html .= '<option id="'.$this->name.sprintf('%02d',$cid+1).'" '.$value.$selected.'>'.$c['title'].'</option> '."\n";
		}
		return $html."</select>\n";
	}


	public function validate($value) {
		foreach ($this->choices as $_k => $_v) {
			if ($_v['value'] == $value) {
				return true;
			}
		}
		return false;
	}
}


class Metroform_ElementCheck extends Metroform_Element {
	public $type = 'check';
	public $choices = array();

	public function addChoice($c,$v='',$selected=0) {
		$top = count($this->choices);
		$this->choices[$top]['title'] = $c;
		if ($v == '') {
			$this->choices[$top]['value'] = sprintf('%02d',$top+1);
		} else {
			$this->choices[$top]['value'] = $v;
		}
		$this->choices[$top]['selected'] = $selected;
		return count($this->choices)-1;
	}

	/**
	 * If only one choice, don't add the array []
	 */
	public function getName() {
		if ( count($this->choices) < 2) {
			return $this->name;
		} else {
			return $this->name.'[]';
		}
	}

	/**
	 * Set an array of 'VALUES' which should be "selected".
	 */
	public function setValue($x) {
		$this->values = $x;
		if(is_array($x)) {
			foreach($this->values as $k=>$v) {
			}
		}
	}

	public function toHtml($attr=array()) {
		$html = '';
		$html .= '<ul class="inputs-list">';
		foreach ($this->choices as $cid => $c) {
			$selected = '';
			if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
			if(is_array($this->values) && in_array($c['value'], $this->values)) { $selected = ' CHECKED="CHECKED" '; }
		$html .= '<li><input type="checkbox" name="'.$this->getName().'" id="'.$this->name.sprintf('%02d',$cid+1).'" value="'.$c['value'].'"'.$selected.'/><label class="label-radio" for="'.$this->name.sprintf('%02d',$cid+1).'">'.$c['title'].'</label></li> ';
		}
		$html .= '</ul>';
		return $html;
	}
}


class Metroform_ElementDate extends Metroform_Element {
	public $type = 'date';

	public function __construct($name,$label=-1, $size=15) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
		$this->size = $size;
	}

	public function toHtml($attr=array()) {
		$html = '<input name="'.$this->name.'" id="'.$this->name.'" size="'.$this->size.'" value="'.$this->value.'" />';
		return $html."&nbsp;<input class=\"popup_cal\" type=\"button\" name=\"".$this->name."_btn\" value=\"Calendar\">\n";
	}
}

class Metroform_Processor {
}


class Metroform_Layout {

	public function renderForm($form) {
		$html = '';
		$html .= '<div class="formContainer">'."\n";
		if ($form->showLabel && $form->label != '' ) {
			$html .= '<p class="cgn_form_header">'.$form->label.'</p>';
			$html .= "\n";
		}
		if ($form->formHeader != '' ) {
			$html .= '<p class="cgn_form_header_content">'.$form->formHeader.'</p>';
			$html .= "\n";
		}

//		$attribs = array('method'=>$form->method, 'name'=>$form->name, 'id'=>$form->id);
		$action = '';
		if ($form->action) {
			$action = ' action="'.$form->action.'" ';
		}
		$html .= '<form accept-charset="UTF-8" class="data_form" method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
		if ($form->enctype) {
			$html .= ' enctype="'.$form->enctype.'"';
		}
		$html .= $this->printStyle($form);
		$html .= '>';
		$html .= "\n";
		$html .= '<table class="cgn_form_table">'."\n";
		foreach ($form->elements as $e) {
			$incss = array('forminput');
			if ($e->required) {
				$incss[] = 'form_req';
			}
			$html .= '<tr><td class="cgn_form_cell_label" valign="top">'."\n";
			$html .= $e->label.'</td><td class="cgn_form_cell_input" valign="top">'."\n";
			if ($e->type == 'textarea') {
				$html .= '<textarea name="'.$e->name.'" id="'.$e->name.'" rows="'.$e->rows.'" cols="'.$e->cols.'" >'.htmlentities($e->value,ENT_QUOTES).'</textarea>'."\n";
			} else if ($e->type != '') {
				$html .= $e->toHtml();
			} else {
				$html .= '<input class="'.implode(' ', $incss).'" type="'.$e->type.'" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'" size="'.$e->size.'"/>'."\n";
			}
			$html .= '</td></tr>'."\n";
		}
		if ($form->formFooter != '') {
			$html .= '<tr><td class="cgn_form_footer_row" colspan="2">'."\n";
				$html .= '<P>'.$form->formFooter.'</P>'."\n";
			$html .= '</td></tr>'."\n";
		}
		$trailingHtml = '';
		if (count($form->hidden)) {
			foreach ($form->hidden as $e) {
				$trailingHtml .= '<input type="hidden" name="'.$e->name.'" id="'.$e->name.'"';
				$trailingHtml .= ' value="'.htmlentities($e->value,ENT_QUOTES).'"/>'."\n";
			}
		}

		if ($form->showSubmit || $form->showCancel) {
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
		}
		if ($trailingHtml !== '') {
			$html .= '<tr><td class="cgn_form_last_row" colspan="2">'."\n";
			$html .= $trailingHtml."\n";
			$html .= '</td></tr>'."\n";
		}

		$html .= '</table>'."\n";
		$html .= '</form>'."\n";
		$html .= '</div>'."\n";

		return $html;
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

