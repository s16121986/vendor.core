<?php
namespace Form\Element;

class Address extends Text{

	protected $_options = array(
		'inputType' => 'address',
		'coordInput' => 'address_coord'
	);

	protected $_attributes = array('placeholder');

	protected function prepareValue($value) {
		return (string)$value;
	}

	public function getHtml() {
		$s = parent::getHtml();
		$s .= '<input type="hidden" name="' . $this->_form->name . '[' . $this->coordInput . ']" value="' . self::escape($this->getValue()) . '" />';
		$jsOptions = array();
		$s .= $this->getJs($jsOptions);
		return $s;
	}


}
