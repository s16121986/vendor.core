<?php
namespace Form\Element;

use Form\Fieldset as AbstractFieldset;

class Fieldset extends AbstractFieldset{

	protected $_attributes = array('readonly');
	protected $_rendered = false;

	public function __construct($name, $options = array()) {
		$options['name'] = $name;
		$options['type'] = 'fieldset';
		$options['render'] = true;
		parent::__construct($options);
	}

	public function render() {
		$html = '<fieldset>';
		if ($this->legend) {
			$html .= '<legend>' . $this->legend . '</legend>';
		}
		$html .= parent::render();
		$html .= '</fieldset>';
		$this->_rendered = true;
		return $html;
	}

	public function isRendered($flag = null) {
		if (null === $flag) {
			return $this->_rendered;
		}
		$this->_rendered = (bool)$flag;
		return $this;
	}

	public function getInputName() {
		if (!isset($this->_options['inputName'])) {
			$name = $this->name;
			if ($this->_parent && $this->_parent->name) {
				switch (true) {
					case $this->_parent instanceof Fieldset:
					case $this->_parent instanceof \Form\Element:
						$name = $this->_parent->getInputName() . '[' . $name . ']';
						break;
					default:
						$name = $this->_parent->getName() . '[' . $name . ']';
				}
			}
			$this->_options['inputName'] = $name;
		}
		return $this->_options['inputName'];
	}

	public function isFileUpload() {
		return $this->hasUpload();
	}

}
