<?php
namespace Api\Attribute\File;

abstract class Plugin{

	protected $_file;

	protected $_options = array();

	public function __construct($options = null) {
		if ($options) {
			$this->setOptions($options);
		}
	}

	public function __get($name) {
		return (isset($this->_options[$name]) ? $this->_options[$name] : null);
	}

	public function setOptions($options) {
		$this->_options = $options;
		return $this;
	}

	public function getFile() {
		return $this->_file;
	}

	public function setFile(\Api\File $file) {
		$this->_file = $file;
		return $this;
	}

}