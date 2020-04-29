<?php

use File\AbstractFile;

class File extends AbstractFile {

	public function __set($name, $value) {
		switch ($name) {
			case 'tmp_name':
				if (!$value)
					break;

				$this->_set('fullname', $value);
				break;
		}
		parent::__set($name, $value);
	}

	public function exists() {
		return parent::exists() || ($this->tmp_name && file_exists($this->tmp_name));
	}

	public function __toString() {
		return $this->name;
	}

}
