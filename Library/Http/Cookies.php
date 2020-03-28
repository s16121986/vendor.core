<?php
namespace Http;

class Cookies{
	
	private static $domain = null;
	
	public static function setDomain($domain) {
		self::$domain = $domain;
	}
	
	public static function get($name, $default = null) {
		return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default);
	}
	
	public static function set($name, $cookie, $time, $path = '/', $host = null, $secure = null, $httpOnly = true) {
		if (null === $secure) {
			$secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
		}
		if (null === $host) $host = self::$domain;
		if (null === $host && isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		}
		if (is_string($time)) {
			$now = now();
			$now->modify($time);
			$time = $now->getTimestamp();
		}if (false && defined('isAdmin')) {
			var_dump('Set-Cookie:'
				. ' ' . $name . '=' . $cookie . ';'
				. ' expires=' . $now->format('D, d-M-Y H:i:s') . ' GMT;'
				. ' path=' . $path . ';'
				. ' domain=' . $host
				. ($secure ? ' secure;' : '')
				. ($httpOnly ? 'HttpOnly ' : ''));
			exit;
		}
		if (false && $host && strpos($host, '.') !== 0) {
			header('Set-Cookie:'
				. ' ' . $name . '=' . $cookie . ';'
				. ' expires=' . $now->format('D, d-M-Y H:i:s') . ' GMT;'
				. ' path=' . $path . ';'
				. ' domain=' . $host);
			return true;
		} else {
			$_COOKIE[$name] = $cookie;
			return setcookie($name, $cookie, $time, $path, $host, $secure, $httpOnly);
		}
	}
	
	public static function clear($name, $path = '/') {
		unset($_COOKIE[$name]);
		return self::set($name, '', time() - 3600, $path);
    }
	
}