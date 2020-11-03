<?php

namespace Form;

use Form\Element;

class Fieldset {

	protected static $_defaultElementOptions = [
		'requiredLabel' => ''
	];

	protected $_elements = [];
	protected $_values = [];
	protected $_parent = null;
	protected $_options = [
		'name' => null,
		'baseParams' => [],
	];

	public static function setDefaults($defaults) {
		self::$_defaultElementOptions = $defaults;
	}

	public function __set($name, $value) {
		$this->setOption($name, $value);
	}

	public function __get($name) {
		switch ($name) {
			case 'name':
				return $this->getName();
			case 'id':
				return $this->getId();
			case 'data':
				return $this->getData();
			case 'errors':
				return $this->getErrors();
		}
		if (isset($this->_options[$name]))
			return $this->_options[$name];
		return $this->getElement($name);
	}

	public function __construct($options = null) {
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}

	public function setOptions($options) {
		foreach ($options as $k => $v) {
			$this->setOption($k, $v);
		}
		return $this;
	}

	public function setOption($key, $option) {
		switch ($key) {
			case 'elements':
				foreach ($option as $k => $el) {
					if (is_array($el)) {
						$type = (isset($el['type']) ? $el['type'] : $this->defaultType);
						$this->addElement($k, $type, $el);
					}
				}
				break;
			default:
				$this->_options[$key] = $option;
		}
		return $this;
	}

	public function setId($method) {
		return $this->setOption('id', $method);
	}

	public function getName() {
		return $this->_options['name'];
	}

	public function getId() {
		if (isset($this->_options['id']))
			return $this->_options['id'];

		return ($this->name ? strtolower(str_replace('\\', '_', get_class($this))) . '_' . $this->name : null);
	}

	public function setParent($parent) {
		$this->_parent = $parent;
		return $this;
	}

	public function getForm() {
		if ($this->_parent) {
			return ($this->_parent instanceof \Form ? $this->_parent : $this->_parent->getForm());
		}
		return null;
	}

	public function addElement($element, $type = null, array $options = []) {
		if (!is_array($options)) {
			$options = [];
		}

		foreach (self::$_defaultElementOptions as $k => $v) {
			if (!isset($options[$k])) {
				$options[$k] = ($this->$k === null ? $v : $this->$k);
			}
		}
		if (is_array($element)) {
			$options = $element;
			$type = $element['type'];
			$element = $element['name'];
			unset($options['name'], $options['type']);
		}
		if (is_string($element) || is_integer($element)) {
			$cls = 'Form\\Element\\' . ucfirst($type);
			/*if (!class_exists($cls, false)) {
				include 'Library/' . str_replace('\\', '/', $cls) . '.php';
			}*/
			$element = new $cls($element, $options);
		} else if ($element instanceof Element) {

		} else {

		}
		$element->setParent($this);
		$this->_elements[$element->name] = $element;
		return $this;
	}

	public function getElement($name) {
		return (isset($this->_elements[$name]) ? $this->_elements[$name] : null);
	}

	public function getElements() {
		return $this->_elements;
	}

	public function getValue($key = null) {
		if (null === $key) {
			return $this->getData();
		}
		if (isset($this->_values[$key])) {
			return $this->_values[$key];
		}
		return (isset($this->_elements[$key]) ? $this->_elements[$key]->getValue() : null);
	}

	public function setValue($key, $value = null) {
		if (null === $value) {
			$this->setData($key);
			return true;
		}
		if (isset($this->_elements[$key])) {
			return $this->_elements[$key]->setValue($value);
		}
		$this->_values[$key] = $value;
		return true;
	}

	public function getData() {
		$data = [];
		foreach ($this->_elements as $element) {
			if ($element->disabled) {
				continue;
			}
			switch ($element->type) {
				case 'label':
					break;
				case 'password':
				case 'file':
				case 'image':
					if (!$element->isEmpty()) {
						$data[$element->name] = $element->getValue();
					}
					break;
				default:
					$data[$element->name] = $element->getValue();
			}
		}
		foreach ($this->_values as $k => $v) {
			$data[$k] = $v;
		}
		if (is_array($this->baseParams)) {
			foreach ($this->baseParams as $k => $v) {
				$data[$k] = $v;
			}
		}
		return $data;
	}

	public function setData($data) {
		foreach ($this->_elements as $element) {
			if (isset($data[$element->name])) {
				$element->setValue($data[$element->name]);
			}
		}
		return $this;
	}

	public function hasUpload() {
		foreach ($this->getElements() as $element) {
			if ($element->isFileUpload()) {
				return true;
			}
		}
		return false;
	}

	public function isValid() {
		if (!empty($this->_errors)) {
			return false;
		}
		foreach ($this->_elements as $element) {
			if (!$element->isValid()) {
				return false;
			}
		}
		return true;
	}

	public function isSubmitted() {
		if (($form = $this->getForm())) {
			return $form->isSubmitted();
		}
		return false;
	}

	public function render() {
		$elements = func_get_args();
		if (empty($elements)) {
			$elements = array_keys($this->_elements);
		} else if (is_array($elements[0])) {
			$elements = $elements[0];
		}
		$html = '';
		foreach ($elements as $k) {
			if (isset($this->_elements[$k]) && $this->_elements[$k]->render && !$this->_elements[$k]->isRendered()) {
				$element = $this->_elements[$k];
				$html .= $this->renderElement($element);
			}
		}
		return $html;
	}

	public function renderElement($element) {
		if (is_string($element)) {
			$element = $this->getElement($element);
			if (!$element) {
				return '';
			}
		} else if (!($element instanceof Element || $element instanceof Fieldset)) {
			return '';
		}
		if (in_array($element->type, ['hidden']) && !$element->label) {
			return $element->render();
		}
		$html = '';
		$error = null;
		$cls = 'form-field field-' . $element->type . ' field-' . $element->name . '';
		if ($this->isSubmitted() && !$element->isValid() && !($element instanceof self)) {
			$cls .= ' field-invalid';
			$error = $element->getError();
		}
		if ($element->required) {
			$cls .= ' field-required';
		}
		$html .= '<div class="' . $cls . '">';
		$renderData = [
			'label' => '',
			'input' => $element->render(),
			'hint' => ($element->hint ? '<div class="form-element-hint">' . $element->hint . '</div>' : ''),
			'error' => ($error && is_string($error) ? '<span class="error">' . $error . '</span>' : '')
		];
		if ($element->label) {
			$renderData['label'] = $element->renderLabel();
		}
		$renderTpl = ($element->renderTpl ? $element->renderTpl : '%label%%input%%error%%hint%');
		foreach ($renderData as $k => $v) {
			$renderTpl = str_replace('%' . $k . '%', $v, $renderTpl);
		}
		$html .= $renderTpl;
		$html .= '</div>';
		return $html;
	}


}