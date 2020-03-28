<?php
namespace Form\Element\Select;

class Item{

	private static $_autoTextKeys = array('text', 'name', 'presentation');
	private static $_autoValueKeys = array('id', 'key', 'value');

	private $_data;

	private static function getAutoKey($autoKeys, $item) {
		foreach ($autoKeys as $k) {
			if (isset($item[$k])) {
				return $item[$k];
			}
		}
	}

	public function __construct($value, $text = '') {
		if (is_array($value)) {
			$data = $value;
			$text = self::getAutoKey(self::$_autoTextKeys, $value);
			$value = self::getAutoKey(self::$_autoValueKeys, $value);
		} else {
			$data = array();
		}
		$data['value'] = $value;
		$data['text'] = $text;
		$this->_data = $data;
	}

	public function __get($name) {
		return (isset($this->_data[$name]) ? $this->_data[$name] : null);
	}
	
	public function __set($name, $value) {
		$this->_data[$name] = $value;
	}

	public function getPresentation() {
		$text = '';
		if ($this->deep) {
			for ($i = 0; $i < $this->deep; $i++) {
				$text .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
		}
		$text .= $this->text;
		return $text;
	}

}