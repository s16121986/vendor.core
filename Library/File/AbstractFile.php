<?php

namespace File;

abstract class AbstractFile {

	protected $data = [];
	protected $content = null;

	public function __construct($data = null) {
		if (!$data)
			return;

		if (is_string($data))
			$data = ['fullname' => $data];

		$this->setData($data);
	}

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
		switch ($name) {
			case 'name':
				if (!$value)
					break;
				
				$tmp = explode('.', $value);
				$this
						->_set('extension', strtolower(array_pop($tmp)))
						->_set('basename', implode('.', $tmp));
				break;
			case 'fullname':
				if (!$value)
					break;
				
				$tmp = explode('/', $value);
				$this
						->_set('name', array_pop($tmp))
						->_set('path', implode('/', $tmp));
				break;
			case 'path':
				if (!$value || $this->fullname || !$this->name)
					break;
				
				$this->_set('fullname', $value . $this->name);
				break;
		}
		
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
			$this->$k = $v;
		}

		return $this;
	}

	public function getData() {
		return $this->data;
	}

	public function getContents() {
		if (null !== $this->content)
			return $this->content;

		if ($this->tmp_name && file_exists($this->tmp_name))
			return file_get_contents($this->tmp_name);

		if ($this->exists())
			return file_get_contents($this->fullname);

		return false;
	}

	public function setContent($content) {
		$this->content = $content;
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
