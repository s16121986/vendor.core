<?php
namespace Form\Element;

class Textarea extends Xhtml{

	protected $_options = array(
		'stripTags' => true
	);

	protected $_attributes = array('placeholder', 'maxlength');

	protected function prepareValue($value) {
		if (is_scalar($value)) {
			$value = (string)$value;
		} else {
			return '';
		}
		if ($this->stripTags) {
			$value = htmlspecialchars($value, ENT_NOQUOTES);
		}
		return trim($value);
	}

    public function getHtml() {
		return '<textarea' . $this->attrToString() . '>' . $this->getValue() . '</textarea>';
	}

}
