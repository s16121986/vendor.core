<?php

namespace Api;

use Db;
use File as BaseFile;
use File\Part as FilePart;
use Exception;

class File extends BaseFile {

	const NESTING_LEVEL = 3;
	const DIRECTORY_NAME_LENGTH = 2;

	private static $config;
	private static $table = 'files';
	private static $tableParts = 'file_parts';
	protected $parts = null;
	protected $flagContent = false;

	public static function setConfig($config) {
		self::$config = $config;
	}

	public static function config($name, $default = null) {
		return isset(self::$config[$name]) ? self::$config[$name] : $default;
	}

	public static function getById($id) {
		return self::getBy('id', $id);
	}

	public static function getByGuid($guid) {
		return self::getBy('guid', $guid);
	}

	public static function getBy($param, $value = null) {
		if (is_string($param)) {
			$params = [];
			$params[$param] = $value;
		} else {
			$params = $param;
		}

		$isValid = false;
		$q = Db::from(self::$table, ['id', 'guid', 'type', 'parent_id', 'name', 'extension', 'mime_type', 'size', 'mtime', 'index', 'created']);

		foreach ($params as $k => $v) {
			if (empty($v))
				continue;

			$q->where($k . '=?', $v);
			$isValid = true;
		}

		if (!$isValid)
			return;

		$data = $q->limit(1)->query()->fetchRow();
		if ($data)
			return new self($data);
	}

	public static function getDestination($guid, $fullPath = true) {
		return self::getPath($guid, $fullPath) . $guid;
	}

	protected static function getPaths($guid) {
		$paths = [];

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
		} while (Db::from(self::$table)->where('guid=?', $guid)->query()->fetchRow());

		return $guid;
	}

	protected static function checkPath($guid) {
		$paths = self::getPaths($guid);

		$dir = FILES_PATH . DIRECTORY_SEPARATOR;

		while (!empty($paths)) {
			$dir = $dir . array_shift($paths) . DIRECTORY_SEPARATOR;
			if (is_dir($dir))
				continue;

			mkdir($dir);

			self::chmod($dir, 'dir');
		}

		return true;
	}

	protected static function chmod($filename, $type) {
		if (($mode = self::config('chmod.' . $type)))
			chmod($filename, octdec($mode));

		if (($group = self::config('group')))
			chgrp($filename, $group);

		if (($user = self::config('user')))
			chown($filename, $user);
	}

	public function __set($name, $value) {
		switch ($name) {
			case 'guid':
				if ($this->guid === $value)
					return;

				if (!$this->guid && !$this->content && $this->exists())
					$this->setContent($this->getContents());

				$this
						->_set('tmp_name', null)
						->_set('path', self::getPath($value, true))
						->_set('fullname', self::getDestination($value, true));
				break;
			case 'path':
			case 'fullname':
				if ($this->guid)
					return;
				break;
		}

		parent::__set($name, $value);
	}

	public function getParts() {
		if (!$this->id)
			return [];

		else if (null !== $this->parts)
			return $this->parts;

		$this->parts = [];

		$q = Db::from(self::$tableParts, ['index'])
				->where('file_id=?', $this->id)
				->query();
		while ($r = $q->fetch()) {
			$part = new FilePart($this, $r);
			$part->init();
			$this->parts[$r['index']] = $part;
		}

		return $this->parts;
	}

	public function addPart($content) {
		if (null === $this->parts)
			$this->parts = [];

		$part = new FilePart($this, [
			'index' => count($this->parts) + 1,
			'data' => $content
		]);
		$part->init();
		$this->parts[] = $part;

		return $this;
	}

	public function getPart($index) {
		$this->getParts();
		return (isset($this->parts[$index]) ? $this->parts[$index] : null);
	}

	public function isNew() {
		return (null === $this->id);
	}

	public function write() {
		if ($this->isEmpty())
			return false;

		$isNew = $this->isNew();

		$data = [];

		if ($isNew) {
			$this->_set('guid', self::getNewGuid());
			$data['guid'] = $this->guid;
			$data['path'] = self::getPath($this->guid, false);
			$data['fullname'] = self::getDestination($this->guid, false);
			foreach (['guid', 'type', 'parent_id'] as $key) {
				$data[$key] = $this->$key;
			}
		}

		foreach (['name', 'extension', 'mime_type', 'size', 'mtime', 'index'] as $key) {
			$data[$key] = $this->$key;
		}

		if ($isNew || $this->flagContent)
			$data['created'] = 'CURRENT_TIMESTAMP';

		$id = Db::write(self::$table, $data, $this->id);
		if (!$id)
			return false;

		if ($this->flagContent) {
			self::checkPath($this->guid);

			if (false === ($fh = fopen($this->fullname, 'w')))
				throw new Exception('Cant create file');

			fwrite($fh, $this->content);
			fclose($fh);

			self::chmod($this->fullname, 'file');

			$this->flagContent = false;
			$this->content = null;
		}

		$this->_set('id', $id);

		if ($isNew) {
			foreach ($this->parts as $part) {
				$fh = fopen($part->fullname, 'w+');
				fwrite($fh, $part->getContents());
				fclose($fh);
				self::chmod($part->fullname, 'file');
				Db::insert(self::$tableParts, [
					'file_id' => $id,
					'name' => $part->name,
					'fullname' => $part->fullname,
					'size' => $part->size,
					'mtime' => $part->mtime,
					'index' => $part->index
				]);
			}
		}

		return $id;
	}

	public function delete() {
		if ($this->isNew())
			return false;

		if ($this->exists())
			$this->unlink();

		if ($this->parts) {
			foreach ($this->parts as $part) {
				if ($part->exists())
					$part->unlink();
			}
			Db::delete(self::$tableParts, 'file_id=' . $this->id);
		}

		return Db::delete(self::$table, 'id=' . $this->id);
	}

	public function reset() {
		$this->parts = null;
		$this->flagContent = false;
		return parent::reset();
	}

	public function setContent($content) {
		$this->flagContent = true;
		return parent::setContent($content);
	}

	public function __toString() {
		return $this->guid;
	}

}
