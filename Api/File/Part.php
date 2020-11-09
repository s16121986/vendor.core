<?php

namespace Api\File;

use File;
use Api\File as ApiFile;

class Part extends File {

	const INDEX_PREFIX = '_';

	protected $file;

	public function __construct(ApiFile $file, $data) {
		$this->file = $file;
		$this->setData($data);
	}

	public function __get($name) {
		switch ($name) {
			case 'fullname':
				return $this->file->path . $this->file->guid . self::INDEX_PREFIX . $this->index;
			case 'basename':
				return $this->file->basename . self::INDEX_PREFIX . $this->index;
			case 'name':
				return $this->file->basename . $this->file->extension;
			case 'extension':
			case 'path':
			case 'mime_type':
			case 'type':
				return $this->file->$name;
		}
		return parent::__get($name);
	}

	public function __toString() {
		return $this->file->guid;
	}

}