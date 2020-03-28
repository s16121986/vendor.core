<?php
namespace Api\File;

class Part extends \File{

	const INDEX_PREFIX = '_';

	protected $_file;

	protected $_data = array();

	public function __construct(\Api\File $file, $data) {
		$this->_file = $file;
		$this->setData($data);
	}

	public function __get($name) {
		switch ($name) {
			case 'extension':
			case 'path':
			case 'mime_type':
			case 'type':
				return $this->_file->$name;
		}
		return parent::__get($name);
	}

	protected function init() {
		$file = $this->_file;
		//$this->_data['extension'] = $file->extension;
		//$this->_data['path'] = $file->path;
		//$this->_data['mime_type'] = $file->mime_type;
		$this
			->_set('basename', $file->basename . self::INDEX_PREFIX . $this->index)
			->_set('fullname', $this->path . $file->guid . self::INDEX_PREFIX . $this->index);
		if ($this->basename) {
			$this->_set('name', $this->basename . '.' . $this->extension);
		}
	}

	public function __toString() {
		return $this->_file->guid;
	}

}