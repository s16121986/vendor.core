<?php
namespace Grid;

abstract class Column{

	private static $_default = array(
		'order' => false,
		'text' => '',
		'emptyText' => ''
	);

	protected $_options = array(
		'renderer' => false
	);

	protected $_grid;

	public function __set($name, $value) {
		$this->setOption($name, $value);
	}

	public function __get($name) {
		if (isset($this->_options[$name])) return $this->_options[$name];
		//if (isset(self::$_default[$name])) return self::$_default[$name];
		return null;
	}

	public function __construct($name, $options = array()) {
		if (!isset($options['id'])) {
			$options['id'] = 'formfield_' . $name;
		}
		if (!isset($options['class'])) {
			$options['class'] = '';
		}
		$options['type'] = strtolower(str_replace('Grid\\Column\\', '', get_class($this)));
		$options['class'] .= ' column-' . $options['type'];
		$this->setName($name)
				->setOptions(array_merge(self::$_default, $options));

	}

	public function setName($name) {
		$this->_options['name'] = $name;
		return $this;
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

	public function formatValue($value, $row = null) {
		return $value;
	}
	
	public function prepareValue($value) {
		return $value;
	}

	public function render($value, $row) {
		$value = $this->formatValue($value, $row);
		$row['value'] = $value;
		if ($this->renderer) {
			$value = call_user_func_array($this->renderer, array($row, $this->params));
		}
		if ($this->renderTpl) {
			$value = \Format::formatTemplate($this->renderTpl, $row);
		}
		if (empty($value)) {
			return $this->emptyText;
		}
		return $value;
	}

}