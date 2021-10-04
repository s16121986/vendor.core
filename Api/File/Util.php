<?php

namespace Api\File;

use Api\File as ApiFile;
use Db;
use Exception;

abstract class Util {

	const NESTING_LEVEL = 3;
	const DIRECTORY_NAME_LENGTH = 2;

	public static $table = 'files';
	public static $tableParts = 'file_parts';
	private static $config;
	private static $httpUrlTpl = '/file/%guid%/';

	public static function setConfig($config) {
		self::$config = $config;
		if (isset($config['table'])) {
			self::$table = $config['table'];
			self::$tableParts = self::$table . '_parts';
		}
	}

	public static function setHttpUrlTemplate($tpl) {
		self::$httpUrlTpl = $tpl;
	}

	public static function config($name, $default = null) {
		switch ($name) {
			case 'table':
				return self::$table;
			case 'table_parts':
				return self::$tableParts;
		}
		return isset(self::$config[$name]) ? self::$config[$name] : $default;
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
		if (!$data)
			return false;

		return new ApiFile($data);
	}

	public static function getById($id) {
		return self::getBy('id', $id);
	}

	public static function getByGuid($guid) {
		return self::getBy('guid', $guid);
	}

	public static function chmod($filename, $type) {
		if (($mode = self::config('chmod.' . $type)))
			chmod($filename, octdec($mode));

		if (($group = self::config('group')))
			chgrp($filename, $group);

		if (($user = self::config('user')))
			chown($filename, $user);
	}

	public static function getPaths($guid) {
		$paths = [];

		for ($i = 0; $i < self::NESTING_LEVEL; $i++) {
			$paths[] = substr($guid, $i * self::DIRECTORY_NAME_LENGTH, self::DIRECTORY_NAME_LENGTH);
		}

		return $paths;
	}

	public static function getNewGuid() {
		do {
			$guid = md5(uniqid());
		} while (Db::from(self::$table)->where('guid=?', $guid)->query()->fetchRow());

		return $guid;
	}

	public static function getPath($guid, $fullPath = true) {
		$path = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, self::getPaths($guid)) . DIRECTORY_SEPARATOR;

		return $fullPath ? FILES_PATH . $path : $path;
	}

	public static function getDestination($guid, $fullPath = true) {
		return self::getPath($guid, $fullPath) . $guid;
	}

	public static function checkPath($guid) {
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

	public static function fwrite($file, $content = null) {
		$filename = $file->getDestination();

		if (!$filename)
			throw new Exception('Cant write empty filename');

		if (false === ($fh = fopen($filename, 'w')))
			throw new Exception('Cant create file');

		fwrite($fh, $content ?? $file->getContents());
		fclose($fh);

		self::chmod($filename, 'file');

		$file->setContent(null);

		return true;
	}

	public static function getHttpUrl($file) {
		$tpl = self::$httpUrlTpl;
		return str_replace('%guid%', (string)$file ?? 'empty', $tpl);
	}

}
