<?php

namespace Exception\Output;

class Log extends \Exception\Output {

	/**
	 * @param Exception $exception
	 * @return string
	 */
	public function format($exception, $html = false) {
		$s = 'http://' . self::getServer('HTTP_HOST') . self::getServer('REQUEST_URI') . "\n" 
			. 'DateTime: ' . date('c') . "\n"
			. 'IP: ' . self::getClientIp() . "\n"
			. 'UserAgent: ' . self::getUserAgent() . "\n"
			. (defined('UserId') ? 'UserId: ' . UserId . "\n" : '')
			. (defined('UserRole') ? 'UserRole: ' . UserRole . "\n" : '')
			. self::getServer('REQUEST_METHOD') . ': ' . http_build_query($_REQUEST) . "\n\n";
		if ($html) {
			$s = '<pre>' . $s . '</pre>';
		}
		return $s . $this->_format($exception, $html);
	}

	private static function getClientIp($checkProxy = true) {
		$ip = null;
		if ($checkProxy && isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != null) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else if ($checkProxy && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != null) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	private static function getUserAgent() {
		return (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
	}
	
	private static function getServer($name, $default = null) {
		return (isset($_SERVER[$name]) ? $_SERVER[$name] : $default);
	}

}