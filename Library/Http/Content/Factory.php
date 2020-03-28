<?php
namespace Http\Content;

use File;
use File\AbstractFile;
use Exception;

abstract class Factory{
	
	private static $assoc = [
		'text/css' => 'TextCss',
		'css' => 'TextCss',
		'application/x-javascript' => 'Javascript',
		'js' => 'Javascript',
		'text/plain' => 'TextPlain'
	];
	
	public static function get($name, $params = null) {
		if (isset(self::$assoc[$name]))
			$name = self::$assoc[$name];
		$cls = __NAMESPACE__ . '\\' . $name;
		return new $cls($params);
	}
	
	public static function fromFile($file) {
		if (is_string($file))
			$file = new File($file);
		if (!($file instanceof AbstractFile)) {
			throw new Exception('File format invalid');
		}
		if (!$file->exists()) {
			return;
		}
		switch (true) {
			case false !== strpos($file->mime_type, 'image'):
				return self::get('Image', $file);
			case false !== strpos($file->mime_type, 'pdf'):
				return self::get('FilePdf', $file);
		}
		return self::get('File', $file);
	}
	
	public static function notFound() {
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
	}
	
}