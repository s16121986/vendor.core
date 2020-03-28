<?php
namespace Api;

use Db;
//use Exception;

class File extends \File{

	const NESTING_LEVEL = 3;
	const DIRECTORY_NAME_LENGTH = 2;

	protected $_model;
	protected $_parts = [];

	public static function getById($id) {
		return self::getBy('id', $id);
	}

	public static function getByGuid($guid) {
		return self::getBy('guid', $guid);
	}

	public static function getBy($param, $value = null) {
		if (is_string($param)) {
			$params = array();
			$params[$param] = $value;
		} else {
			$params = $param;
		}
		$isValid = false;
		$q = Db::from('files', '*');
		foreach ($params as $k => $v) {
			if (empty($v)) {
				continue;
			}
			$q->where($k . '=?', $v);
			$isValid = true;
		}
		if (!$isValid) {
			return;
		}
		$data = $q->limit(1)->query()->fetchRow();
		if ($data) {
			return new self($data);
		}
	}

	public static function getDestination($guid, $fullPath = true) {
		return self::getPath($guid, $fullPath) . $guid;
	}

	protected static function getPaths($guid) {
		$paths = array();
		for ($i = 0; $i < self::NESTING_LEVEL; $i++) {
			$paths[] = substr($guid, $i * self::DIRECTORY_NAME_LENGTH, self::DIRECTORY_NAME_LENGTH);
		}
		return $paths;
	}

	protected static function getPath($guid, $fullPath = true) {
		$path = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, self::getPaths($guid)) . DIRECTORY_SEPARATOR;
		return $fullPath ? FILES_PATH . $path : $path;
	}

	protected static function getNewGuid() {
		do {
			$guid = md5(uniqid());
		} while (Db::from('files')->where('guid=?', $guid)->query()->fetchRow());
		return $guid;
	}

	protected static function checkPath($guid) {
		$paths = self::getPaths($guid);
		$dir = FILES_PATH . DIRECTORY_SEPARATOR;
		while (!empty($paths)) {
			$dir = $dir . array_shift($paths) . DIRECTORY_SEPARATOR;
			if (!is_dir($dir)) {
				mkdir($dir);
			}
		}
		return true;
	}

	protected function init() {
		if (!$this->guid)
			return;
		
		$this->_set('path', self::getPath($this->guid, true));
		$name = explode('.', $this->name);
		$this
			->_set('extension', strtolower(array_pop($name)))
			->_set('basename', implode('.', $name))
			->_set('fullname', self::getDestination($this->guid, true)/* . '.' . $this->extension */);

		foreach ($this->_parts as $part) {
			$part->init();
		}

		if ($this->id) {
			$parts = Db::from('file_parts', array('index'))
					->where('file_id=?', $this->id)
					->query()->fetchAll();
			foreach ($parts as $part) {
				$this->_parts[$part['index']] = new File\Part($this, $part);
			}
		}
	}

	public function setModel(\Api $monel) {
		$this->_model = $monel;
		return $this;
	}

	public function setType($type) {
		$this->_set('type', $type);
		return $this;
	}

	public function addPart($content) {
		$this->_parts[] = new File\Part($this, array(
			'index' => count($this->_parts) + 1,
			'data' => $content
		));
		return $this;
	}

	public function getPart($index) {
		return (isset($this->_parts[$index]) ? $this->_parts[$index] : null);
	}

	public function isNew() {
		return (null === $this->id);
	}

	public function write() {
		if ($this->isEmpty()) {
			return false;
		}
		if ($this->isNew()) {
			$this->_set('guid', self::getNewGuid());
			$this->init();
			if ($this->exists()) {
				$this->unlink();
			}
			self::checkPath($this->guid);

			if (false === ($fh = fopen($this->fullname, 'w+'))) {
				throw new Exception('Cant create file');
			}
			fwrite($fh, $this->getContents());
			fclose($fh);

			if ($this->_model && !$this->_model->isNew()) {
				$this->_set('parent_id', $this->_model->id);
			}
			/* if (defined('UserId') && UserId) {
			  $this->_set('author_id', UserId);
			  } */
		}
		$data = [];
		$data['path'] = self::getPath($this->guid, false);
		$data['fullname'] = self::getDestination($this->guid, false);
		foreach (['guid', 'type', 'parent_id', 'name', 'extension', 'mime_type', 'size', 'mtime', 'index'] as $key) {
			if (null !== $this->$key) {
				$data[$key] = $this->$key;
			}
		}
		
		if ($this->isNew()) {
			$data['created'] = 'CURRENT_TIMESTAMP';
		}

		$id = Db::write('files', $data, $this->id);
		if ($id) {
			if ($this->isNew()) {
				foreach ($this->_parts as $part) {
					$fh = fopen($part->fullname, 'w+');
					fwrite($fh, $part->getContents());
					fclose($fh);
					Db::insert('file_parts', array(
						'file_id' => $id,
						'name' => $part->name,
						'fullname' => $part->fullname,
						'size' => $part->size,
						'mtime' => $part->mtime,
						'index' => $part->index
					));
				}
			}
			$this->_set('id', $id);
		}
		return $id;
	}

	public function delete() {
		if (!$this->isNew()) {
			if ($this->exists()) {
				$this->unlink();
			}
			if ($this->_parts) {
				foreach ($this->_parts as $part) {
					if ($part->exists()) {
						$part->unlink();
					}
				}
				Db::delete('file_parts', 'file_id=' . $this->id);
			}
			return Db::delete('files', 'id=' . $this->id);
		}
		return false;
	}

	public function __toString() {
		return $this->guid;
	}

}
