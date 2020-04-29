<?php

use File\AbstractFile;

class File extends AbstractFile {

	protected function init() {
		if ($this->fullname) {
			$name = explode('/', $this->fullname);
			$this
					->_set('name', array_pop($name))
					->_set('path', implode('/', $name));
		} elseif ($this->path)
			$this->_set('fullname', $this->path . $this->name);
		elseif ($this->tmp_name)
			$this->_set('fullname', $this->tmp_name);

		if ($this->name) {
			$name = explode('.', $this->name);
			$this
					->_set('extension', strtolower(array_pop($name)))
					->_set('basename', implode('.', $name));
		}

		return $this;
	}

	public function __toString() {
		return $this->name;
	}

}
