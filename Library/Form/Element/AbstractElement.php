<?php
namespace Form\Element;

abstract class AbstractElement{

	private static $_default = array(
		'required' => false,
		'render' => true
	);
	protected $_options = array();
	protected $_rendered = false;
	protected $_error;
	protected $_value = null;
	protected $_label = null;
	protected $_parent;
	protected $_attributes = array();

	public static function factory($name, $type, $options = null) {
		if (!is_array($options)) {
			$options = array();
		}
		$cls = 'Form\\Element\\' . ucfirst($type);
		$element = new $cls($name, $options);
		return $element;
	}

	public function __set($name, $value) {
		$this->setOption($name, $value);
	}

	public function __get($name) {
		return $this->getOption($name);
	}

	public function __construct($name, $options = array()) {
		if (!isset($options['class'])) {
			$options['class'] = '';
		}
		$options['type'] = strtolower(str_replace('Form\\Element\\', '', get_class($this)));
		$options['class'] .= ' field-' . $options['type'];
		$this->setName($name)
				->setOptions(array_merge(self::$_default, $options));
		$this->init();
	}

	public function setOptions($options) {
		$lastOptions = array();
		foreach (array('default', 'value') as $k) {
			if (isset($options[$k])) {
				$lastOptions[$k] = $options[$k];
				unset($options[$k]);
			}
		}
		foreach ($options as $k => $v) {
			$this->setOption($k, $v);
		}
		foreach ($lastOptions as $k => $v) {
			$this->setOption($k, $v);
		}
		return $this;
	}
	
	public function getOption($name) {
		switch ($name) {
			case 'value':return $this->getValue();
			case 'id':return $this->getId();
			case 'inputName':return $this->getInputName();
		}
		if (isset($this->_options[$name])) {
			return $this->_options[$name];
		}
		//if (isset(self::$_default[$name])) return self::$_default[$name];
		return null;
	}

	public function setOption($key, $option) {
		switch ($key) {
			case 'value':$this->setValue($option);break;
			case 'default':
				$this->_options[$key] = $this->prepareValue($option);
				$this->setValue($option);
				break;
			default:
				$this->_options[$key] = $option;
		}
		return $this;
	}

	public function setParent($parent) {
		$this->_parent = $parent;
		/*if ($parent instanceof \Form) {
			$this->id = $this->_parent->getName() . '_formfield_' . $this->name;
		} else {
			$this->id = $this->_parent->id . '_' . $this->name;
		}*/
		return $this;
	}

	public function getForm() {
		return ($this->_parent instanceof \Form ? $this->_parent : null);
	}

	public function setForm($form) {
		return $this->setParent($form);
	}
	
	public function getId() {
		if (!isset($this->_options['id'])) {
			$parts = array();
			if ($this->_parent && $this->_parent->getId()) {
				$parts[] = $this->_parent->getId();
			}
			$parts[] = $this->name;
			$this->setId(implode('_', $parts));
		}
		return $this->_options['id'];
	}
	
	public function setId($id) {
		return $this->setOption('id', $id);
	}

	public function getInputName() {
		if (!isset($this->_options['inputName'])) {
			$name = $this->name;
			if ($this->_parent && $this->_parent->name) {
				switch (true) {
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
	
	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->_options['name'] = $name;
		return $this;
	}

	public function checkValue($value) {
		return true;
	}

	public function getValuePresentation() {
		return $this->getValue();
	}

	public function getValue() {
		if (null === $this->_value && null !== $this->default) {
			//return $this->default;
		}
		return $this->_value;
	}

	public function setValue($value) {
		if ($this->checkValue($value)) {
			$this->_value = $this->prepareValue($value);
			$this->setError(null);
			return true;
		} elseif (null !== $value) {
			$this->_value = null;
			if ($this->required) {
				$this->setError(true);
			} else {
				$this->setError(null);
			}
		}
		return false;
	}

	public function getLabel() {
		if (null === $this->_label) {
			$this->_label = new Label(array_merge($this->_options, array('text' => $this->label)));
			$this->_label->setElement($this);
		}
		return $this->_label;
	}

	public function setLabel($label) {
		$this->_label = $label;
		return $this;
	}

	public function getError() {
		if ($this->_error) {
			return $this->_error;
		}
		if (($this->required && !$this->disabled && $this->isEmpty())) {
			return 'Это поле должно быть заполнено';
		}
		return null;
	}

	public function setError($error) {
		$this->_error = $error;
		return $this;
	}

	public function isEmpty() {
		return empty($this->_value);
	}

	public function isValid() {
		if ($this->_error || ($this->required && !$this->disabled && $this->isEmpty())) {
			return false;
		}
		return true;
	}

	public function isRendered($flag = null) {
		if (null === $flag) {
			return $this->_rendered;
		}
		$this->_rendered = (bool)$flag;
		return $this;
	}

	public function renderLabel() {
		return $this->getLabel()->render();
	}

	public function renderInput() {
		return $this->getHtml();
	}

	public function isFileUpload() {
		return false;
	}

	public function reset() {
		$this->_value = null;
		$this->_error = null;
		$this->_rendered = false;
		return $this;
	}

	protected function prepareValue($value) {
		return $value;
	}

	protected function attrToString() {
		$attr = array();

		$attr[] = 'name="' . $this->getInputName() . '"';
		foreach (array_merge(array('id', 'class'), $this->_attributes) as $k) {
			if ($this->$k) {
				$attr[] = $k . '="' . $this->$k . '"';
			}
		}
		if ($this->disabled) {
			$attr[] = 'disabled="disabled"';
		}
		if ($this->attr) {
			$attr[] = $this->attr;
		}
		return ' ' . implode(' ', $attr);
	}
	
	protected function init() {
		if (null !== $this->default) {
			$this->setValue($this->default);
		}
	}
				

	public static function escape($val) {
		if (is_array($val)) {
			$val = implode(',', $val);
		}
		return str_replace('"', '&quot;', $val);
	}

	public function render() {
		$this->_rendered = true;
		return $this->getHtml();
	}

}