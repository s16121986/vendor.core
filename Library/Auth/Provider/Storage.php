<?php
namespace Auth\Provider;

use Http\Session as HttpSession;

class Storage{
	
	const STORE = 'AP::STORE';
	const CONFIG = 'AP::CONFIG';
	
	protected $provider;
	
	public function __construct($provider) {
		$this->provider = $provider;
		HttpSession::start();
		HttpSession::register(self::STORE, []);
	}
	
	public function set($name, $value) {
		if (null === $value) {
			return $this->delete($name);
		} else {
			$_SESSION[self::STORE][$this->getSessionKey($name)] = $value;
		}
		return $this;
	}
	
	public function get($name) {
		$name = $this->getSessionKey($name);
		return (isset($_SESSION[self::STORE][$name]) ? $_SESSION[self::STORE][$name]: null);
	}
	
	public function delete($name) {
		unset($_SESSION[self::STORE][$this->getSessionKey($name)]);
		return $this;
	}
	
	public function clear() {
		self::deleteMatch($this->provider->name . '.');
		return $this;
	}
	
	private function getSessionKey($name) {
		return strtolower($this->provider->name . '.' . $name);
	}
	
	public static function config($key, $value = null) {
		HttpSession::register(self::CONFIG, []);
		$key = strtolower($key);
		if ($value) {
			$_SESSION[self::CONFIG][$key] = serialize($value);
		} elseif (isset($_SESSION[self::CONFIG][$key])) {
			return unserialize($_SESSION[self::CONFIG][$key]);
		}
		return null;
	}

	private static function deleteMatch($key) {
		$key = strtolower($key);
		if (isset($_SESSION[self::STORE]) && count($_SESSION[self::STORE])) {
			$f = $_SESSION[self::STORE];
			foreach ($f as $k => $v) {
				if (0 === strpos($k, $key)) {
					unset($f[$k]);
				}
			}
			$_SESSION[self::STORE] = $f;
		}
	}
	
}