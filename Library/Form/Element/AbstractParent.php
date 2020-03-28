<?php
namespace Form\Element;

abstract class AbstractParent extends Text{

	protected $_options = [
		'elements' => [],
		'defaultType' => 'number',
		'allowEmpty' => true
	];
	
	public function __construct($name, $options = []) {
		parent::__construct($name, $options);
		if ($this->label && $this->elements) {
			$this->getLabel()->for = $this->elements[0]->id;
		}
	}

	public function addElement($element, $type = null, $options = null) {
		if (!is_array($options)) {
			$options = array();
		}
		if (is_string($element) || is_integer($element)) {
			$cls = '\\Form\\Element\\' . ucfirst($type);
			$element = new $cls($element, $options);
		} elseif ($element instanceof \Form\Element) {

		} else {

		}
		$element->setParent($this);
		$this->_options['elements'][$element->name] = $element;
		return $this;
	}

	public function setOption($key, $option) {
		switch ($key) {
			case 'elements':
				$elements = array();
				foreach ($option as $k => $el) {
					if (is_array($el)) {
						$type = (isset($el['type']) ? $el['type'] : $this->defaultType);
						$el = self::factory($k, $type, $el);
						$el->setParent($this);
						$elements[] = $el;
					}
				}
				$option = $elements;
				break;
		}
		return parent::setOption($key, $option);
	}

	public function __get($name) {
		if (isset($this->_value[$name])) {
			return $this->_value[$name];
		}
		return parent::__get($name);
	}

	public function checkValue($value) {
		return is_array($value);
	}

	protected function prepareValue($value) {
		if (is_array($value)) {
			$isEmpty = true;
			$valueArr = array();
			foreach ($this->elements as $element) {
				$key = $element->name;
				if (isset($value[$key])) {
					$element->setValue($value[$key]);
				}
				$valueArr[$key] = $element->getValue();
				if (null !== $element->getValue()) {
					$isEmpty = false;
				}
			}
			if (!$isEmpty) {
				return $valueArr;
			}
		}
		return null;
	}

	public function isEmpty() {
		return empty($this->getValue());
	}
	
	public function setValue($value) {
		foreach ($this->elements as $element) {
			$key = $element->name;
			$element->setValue(isset($value[$key]) ? $value[$key] : null);
		}
		return true;
	}
	
	public function getValue() {
		$value = parent::getValue();
		$isEmpty = true;
		$valueArr = array();
		foreach ($this->elements as $element) {
			$key = $element->name;
			/*if (isset($value[$key])) {
				$element->setValue($value[$key]);
			}*/
			$valueArr[$key] = $element->getValue();
			if (null !== $element->getValue()) {
				$isEmpty = false;
			}
		}
		if (!$isEmpty) {
			return $valueArr;
		}
		return null;
	}
	
	public function renderElements() {
		$html = '';
		$form = $this->getForm();
		foreach ($this->elements as $element) {
			$cls = 'form-field field-' . $element->type . ' field-' . $element->name . '';
			if ($form && $form->isSubmitted() && !$element->isValid()) {
				$cls .= ' field-invalid';
			}
			if ($element->required) {
				$cls .= ' field-required';
			}
			$html .= '<div class="' . $cls . '">';
			if ($element->label) {
				$html .= $element->renderLabel();
			}
			$html .= $element->getHtml();
			$html .= '</div>';
		}
		return $html;
	}

	public function getHtml() {
		$html = '<div class="elements">';
		$html .= $this->renderElements();
		$html .= '</div>';
		return $html;
	}

}
