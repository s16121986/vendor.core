<?php
abstract class Autoload{
	
	private static $paths = [];
	
	public static function setIncludePath($path) {
		set_include_path($path);
	}
	
	public static function addPath($path, $class, $replace = null) {
		$pathElement = new stdClass();
		$pathElement->path = $path;
		$pathElement->class = $class;
		$pathElement->replace = $replace;

		foreach (self::$paths as $i => $pe) {
			if (0 !== strpos($class, $pe->class))
				continue;
			array_splice(self::$paths, $i, 0, [$pathElement]);
			return;
		}
		self::$paths[] = $pathElement;
	}
	
	public static function getPaths() {
		return self::$paths;
	}
	
	public static function init($defaultPath) {
		spl_autoload_register(function($class) use($defaultPath) {
			foreach (Autoload::getPaths() as $path) {
				if ($path->class && 0 !== strpos($class, $path->class))
						continue;
				$cls = $class;
				if ($path->replace !== null)
					$cls = str_replace($path->class, $path->replace, $cls);
				return Autoload::include($cls, $path->path, false);
			}
			return Autoload::include($class, $defaultPath, false);
		});
	}
	
	public static function includePath($path) {
		if (($dh = opendir($path))) {
			while (($file = readdir($dh)) !== false) {
				if (false !== strpos($file, '.php'))
					include $path . DIRECTORY_SEPARATOR . $file;
			}
			closedir($dh);
		}
	}
	
	private static function include($class, $path, $exception = false) {
		//$parts = explode('\\', $class);
		$filename = $path . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
		if (!file_exists($filename)) {
			if (!$exception)
				return false;
			throw new Exception('Class (' . $class . ';' . $filename . ') not found');
		}
		require $filename;
		return true;
	}
	
}