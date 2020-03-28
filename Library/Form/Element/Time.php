<?php
namespace Form\Element;

class Time extends Text{

	protected $_options = array(
		'inputType' => 'text',
		'placeholder' => '00:00',
		'clockFormat' => true
	);

	public function checkValue($value) {
		return ('' === $value || preg_match('/\d{2}:\d{2}/', $value));
	}

	protected function prepareValue($value) {
		if ($value && preg_match('/\d{2}:\d{2}/', $value)) {
			return $value;
		}
		return null;
	}

	public function isEmpty() {
		return ('00:00' === $this->_value || empty($this->_value));
	}

	public function getHtml() {
		$s = parent::getHtml();
		$s .= $this->getJs($this->_options);
		return $s;
	}

}
