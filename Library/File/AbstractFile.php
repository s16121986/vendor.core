<?php
namespace File;

abstract class AbstractFile{

	protected $_data = array();

	public function __construct($data = null) {
		if ($data) {
			if (is_string($data)) {
				$data = array(
					'fullname' => $data
				);
			}
			$this->setData($data);
		}
	}

	abstract protected function init();

	public function __get($name) {
		switch ($name) {
			case 'size':return $this->getSize();
			case 'mtime':return $this->mtime();
			case 'mtype':
			case 'mime_type':
				return Util::getMimeType($this);
		}
		return (isset($this->_data[$name]) ? $this->_data[$name] : null);
	}

	public function __set($name, $value) {
		$this->_set($name, $value);
	}

	protected function _set($name, $value) {
		$this->_data[$name] = $value;
		return $this;
	}

	public function getSize() {
		return ($this->exists() ? filesize($this->fullname) : false);
	}
	
	public function mtime() {
		return ($this->exists() ? filemtime($this->fullname) : false);
	}
	
	public function touch($time = null, $atime = null) {
		return touch($this->fullname, $time, $atime);
	}
	
	public function unlink() {
		return unlink($this->fullname);
	}

	public function setData($data) {
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$this->_data[$k] = $v;
			}
		}
		$this->init();
		return $this;
	}

	public function getData() {
		return $this->_data;
	}

	public function getContents() {
		if (isset($this->_data['data'])) {
			return $this->_data['data'];
		}
		if ($this->tmp_name && file_exists($this->tmp_name)) {
			return file_get_contents($this->tmp_name);
		}
		if ($this->exists()) {
			return file_get_contents($this->fullname);
		}
		return false;
	}

	public function setContent($content) {
		$this->_data['data'] = $content;
		$this->init();
		return $this;
	}

	public function isEmpty() {
		return empty($this->_data);
	}

	public function exists() {
		return (!$this->isEmpty() && file_exists($this->fullname));
	}
	
	public function reset() {
		$this->_data = array();
		return $this;
	}

}