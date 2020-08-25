<?php

class File {

	protected $data = [];
	protected $content = null;

	protected function set($name, $value) {
		$this->data[$name] = $value;
		return $this;
	}

	public function __construct($data = null) {
		if (null === $data)
			return;

		$this->setData($data);
	}

	public function __get($name) {
		switch ($name) {
			case 'basename':
				return $this->name;
			case 'dirname':
				return $this->path;
			case 'destination':
				return $this->fullname;
			case 'filename':
				return $this->basename;
			case 'mtype':
				return $this->myme_type;
		}

		if (isset($this->data[$name]))
			return $this->data[$name];

		switch ($name) {
			case 'basename':
				return $this->basename();
			case 'size':
				return $this->getSize();
			case 'mtime':
				return $this->getTimeModified();
			case 'mime_type':
				return $this->getMimeType();
			case 'content':
				return $this->content;
		}
	}

	public function setDestination($destination) {
		$info = pathinfo($destination);

		if (isset($info['extension']))
			$this->set('extension', $info['extension']);

		return $this
						->set('path', $info['dirname'])
						->set('filename', $info['filename'])
						->set('name', $info['basename'])
						->set('fullname', $destination);
	}

	public function setName($name) {
		$info = pathinfo($name);

		if (isset($info['extension']))
			$this->set('extension', $info['extension']);

		return $this
						->set('filename', $info['filename'])
						->set('name', $info['basename']);
	}

	public function setData($data) {
		if (is_string($data))
			$this->setDestination($data);
		else if (is_array($data)) {
			$set = function () use ($data) {
				$args = func_get_args();
				$fn = array_shift($args);
				foreach ($args as $n) {
					if (!isset($data[$n]))
						continue;
					$this->$fn($data[$n]);
					break;
				}
			};
			$set->bindTo($this);
			$this->data = $data;
			$set('setDestination', 'destination', 'fullname', 'tmp_name');
			$set('setName', 'name', 'basename');
		} else if ($data instanceof self) {
			$this->data = $data->data;
			$this->content = $data->content;
		}
	}

	public function getData() {
		return $this->data;
	}

	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	public function getContents() {
		if (null !== $this->content)
			return $this->content;

		if ($this->exists())
			return file_get_contents($this->fullname);

		return false;
	}

	public function hasContent() {
		return null !== $this->content || $this->exists();
	}

	public function getSize() {
		if (null !== $this->content)
			return strlen($this->content);

		return $this->exists() ? filesize($this->fullname) : false;
	}

	public function getMimeType() {
		$finfo = new \finfo(FILEINFO_MIME_TYPE);

		if ($this->content)
			return $finfo->buffer($this->content);
		else if ($this->fullname && $this->exists())
			return $finfo->file($this->fullname);

		return null;
	}

	public function getTimeModified() {
		return ($this->exists() ? filemtime($this->fullname) : false);
	}

	public function isEmpty() {
		return empty($this->data);
	}

	public function reset() {
		$this->data = [];
		$this->content = null;
		return $this;
	}

	public function basename(string $suffix = '') {
		return basename($this->fullname, $suffix);
	}

	public function exists() {
		return $this->fullname && file_exists($this->fullname);
	}

	public function touch($time = null, $atime = null) {
		return touch($this->fullname, $time, $atime);
	}

	public function unlink() {
		return unlink($this->fullname);
	}

	public function __toString() {
		return (string) $this->name;
	}

}

/*use File\AbstractFile;

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

}*/
