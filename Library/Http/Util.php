<?php

namespace Http;

abstract class Util {

	public static function isSearchBot($userAgent = null) {
		/* Эта функция будет проверять, является ли посетитель роботом поисковой системы */
		if (null === $userAgent)
			$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$bots = [
			'bot',
			'slurp',
			'crawler',
			'spider',
			'curl',
			'facebook',
			'fetch',
			'okhttp',
			'Chrome-Lighthouse'
		];
		foreach ($bots as $bot) {
			if (stripos($userAgent, $bot) !== false) {
				//$botname = $bot;
				return true;
			}
		}
		return false;
	}

	public static function getHeader($name) {

		// Try to get it from the $_SERVER array first
		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
		if (isset($_SERVER[$temp]) && $_SERVER[$temp])
			return $_SERVER[$temp];
		else if (isset($_SERVER['REDIRECT_' . $temp]) && $_SERVER['REDIRECT_' . $temp])
			return $_SERVER['REDIRECT_' . $temp];

		// This seems to be the only way to get the Authorization header on
		// Apache
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			$name = strtolower($name);
			foreach ($headers as $k => $v) {
				if (strtolower($k) == $name) {
					return $v;
				}
			}
		} else if (function_exists('getallheaders'))
			return getallheaders();

		return false;
	}

	public static function getHeaders() {
		if (function_exists('apache_request_headers'))
			return apache_request_headers();
		else if (function_exists('getallheaders'))
			return getallheaders();
		return [];
	}

	public static function getUserAgent() {
		if (!isset($_SERVER['HTTP_USER_AGENT']))
			return '';
		return $_SERVER['HTTP_USER_AGENT'];
	}

	public static function getClientIp($checkProxy = true) {
		$ip = null;
		if ($checkProxy && isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != null) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else if ($checkProxy && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != null) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

}
