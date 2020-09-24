<?php

namespace Http;

class Cookies {

	private static $domain = null;
	private static $options = [
		'path' => '/',
		'domain' => '', // leading dot for compatibility or use subdomain
		'secure' => false, // or false
		'httponly' => true, // or false
		'samesite' => 'None' // None || Lax || Strict
	];

	public static function setDomain($domain) {
		self::$domain = $domain;
	}

	public static function setOptions(array $options) {
		foreach ($options as $k => $v) {
			self::$options[$k] = $v;
		}
	}

	public static function get($name, $default = null) {
		return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default);
	}

	public static function set($name, $cookie, $time, $path = '/', $host = null, $secure = null, $httpOnly = true) {
		if (null === $secure)
			$secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');

		if (null === $host)
			$host = self::$domain;
		if (null === $host && isset($_SERVER['HTTP_HOST']))
			$host = $_SERVER['HTTP_HOST'];

		if (is_string($time)) {
			$now = now();
			$now->modify($time);
			$time = $now->getTimestamp();
		}

		$_COOKIE[$name] = $cookie;
		return setcookie($name, $cookie, array_merge(self::$options, [
			'expires' => $time,
			'path' => $path,
			'domain' => $host,
			'secure' => $secure,
			'httponly' => $httpOnly
		]));
	}

	public static function clear($name, $path = '/') {
		unset($_COOKIE[$name]);
		return self::set($name, '', time() - 3600, $path);
	}

}