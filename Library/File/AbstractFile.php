<?php

namespace File;

abstract class AbstractFile {

	protected $data = [];

	public function __construct($data = null) {
		if (!$data)
			return;

		if (is_string($data))
			$data = ['fullname' => $data];

		$this->setData($data);
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
		return (isset($this->data[$name]) ? $this->data[$name] : null);
	}

	public function __set($name, $value) {
		$this->_set($name, $value);
	}

	protected function _set($name, $value) {
		$this->data[$name] = $value;
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

	public function setData(array $data) {
		foreach ($data as $k => $v) {
			$this->data[$k] = $v;
		}
		$this->init();
		return $this;
	}

	public function getData() {
		return $this->data;
	}

	public function getContents() {
		if (isset($this->data['data']))
			return $this->data['data'];

		if ($this->tmp_name && file_exists($this->tmp_name))
			return file_get_contents($this->tmp_name);

		if ($this->exists())
			return file_get_contents($this->fullname);

		return false;
	}

	public function setContent($content) {
		$this->data['data'] = $content;
		$this->init();
		return $this;
	}

	public function isEmpty() {
		return empty($this->data);
	}

	public function exists() {
		return !$this->isEmpty() && file_exists($this->fullname);
	}

	public function reset() {
		$this->data = [];
		return $this;
	}

}
