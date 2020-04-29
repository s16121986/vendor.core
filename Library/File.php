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

	public function __toString() {
		return $this->name;
	}

}
