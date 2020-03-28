<?php
namespace Form;

class Label{

	protected $_options = array(
		'requiredLabel' => ''
	);

	protected $_element;

	public function __set($name, $value) {
		$this->setOption($name, $value);
	}

	public function __get($name) {
		if (isset($this->_options[$name])) return $this->_options[$name];
		//if (isset(self::$_default[$name])) return self::$_default[$name];
		return null;
	}

	public function setFor($for) {
		$this->_options['for'] = $for;
		return $this;
	}

	public function __construct($options = array()) {
		$this->setOptions($options);
	}

	public function setOptions($options) {
		foreach ($options as $k => $v) {
			$this->setOption($k, $v);
		}
		return $this;
	}

	public function setOption($key, $option) {
		$this->_options[$key] = $option;
		return $this;
	}

	public function setElement($element) {
		$this->_element = $element;
		$this->setFor($element->id);
		return $this;
	}

	protected function attrAsString() {
		$attr = array();
		$class = array('form-element-label');
		if ($this->_element) {
			$class[] = $this->_element->class;
		}
		if ($this->_element && $this->_element->getForm() && $this->_element->getForm()->isSubmitted() && !$this->_element->isValid()) {
			$class[] = 'invalid-field-label';
		}
		$attr[] = 'class="' . implode(' ', $class) . '"';
		return ' ' . implode(' ', $attr);
	}

	public function render() {
		return '<label for="' . $this->for . '"' . $this->attrAsString() . '>'
				. $this->text
				. ($this->requiredLabel && $this->_element && $this->_element->required ? ' <span class="required-label">' . $this->requiredLabel . '</span>' : '')
			. '</label>';
	}

}