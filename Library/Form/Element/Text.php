<?php
namespace Form\Element;

class Text extends Xhtml{

	protected $_options = array(
		'inputType' => 'text'
	);

	protected $_attributes = array('placeholder', 'autocomplete', 'readonly', 'title', 'maxlength');

	protected function prepareValue($value) {
		return trim(filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW));
	}

	public function getHtml() {
		return '<input type="' . $this->inputType . '"' . $this->attrToString() . ' value="' . self::escape($this->getValue()) . '" />';
	}

}
