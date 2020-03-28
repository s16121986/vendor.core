<?php
namespace Config;

class INI extends Config{
	
	public function __construct($filename) {
		if (is_file($filename)) {
			if (defined('INI_SCANNER_TYPED'))
				$data = parse_ini_file($filename, true, INI_SCANNER_TYPED);
			else {
				$data = self::parseTyped(parse_ini_file($filename, true, INI_SCANNER_RAW));
			}
			if ($data) {
				foreach ($data as $k => $v) {
					$this->set($k, $v);
				}
			}
		}/* elseif (is_dir($filename)) {
			if ($handle = opendir($filename)) {
				while (false !== ($entry = readdir($handle))) {
					if (substr($entry, -4) === '.ini')
						self::fromINI($filename . '/' . $entry);
				}
				closedir($handle);
			}
		} else {
			
		}*/
	}
	
	public function get($name, $default = null) {
		$data = $this->data;
		$dp = [];
		$paths = explode('.', $name);
		while ($paths) {
			$dp[] = array_shift($paths);
			$s = implode('.', $dp);
			if (isset($data[$s])) {
				$data = $data[$s];
				$dp = [];
			} elseif (!$paths) {
				return $default;
			}
		}
		return $data ?: $default;
	}
	
	private static function parseTyped(array $array) {
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				$array[$k] = self::parseTyped($v);
			} elseif (is_string($v)) {
				$vs = strtolower($v);
				switch (true) {
					case $vs === 'null': $v = null; break;
					case $vs === 'on':
					case $vs === 'yes':
					case $vs === 'true': $v = true; break;
					case $vs === 'off':
					case $vs === 'none':
					case $vs === 'false': $v = false; break;
					case is_numeric($v): $v = (float)$v; break;
				}
				$array[$k] = $v;
			}
		}
		return $array;
	}
	
}