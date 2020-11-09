<?php
namespace Api\File;

use File;
use Api\File as ApiFile;

class Part extends File{

	const INDEX_PREFIX = '_';

	protected $file;

	public function __construct(ApiFile $file, $data) {
		$this->file = $file;
		$this->setData($data);
	}

	public function __get($name) {
		switch ($name) {
			case 'extension':
			case 'path':
			case 'mime_type':
			case 'type':
				return $this->file->$name;
		}
		return parent::__get($name);
	}

	public function init() {
		$file = $this->file;
		//$this->_data['extension'] = $file->extension;
		//$this->_data['path'] = $file->path;
		//$this->_data['mime_type'] = $file->mime_type;
		$this
			->set('basename', $file->basename . self::INDEX_PREFIX . $this->index)
			->set('fullname', $this->path . $file->guid . self::INDEX_PREFIX . $this->index);
		
		if ($this->basename)
			$this->set('name', $this->basename . '.' . $this->extension);
	}

	public function __toString() {
		return $this->file->guid;
	}

}