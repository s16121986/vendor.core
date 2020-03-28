<?php
namespace Http;

abstract class Session{
	
	const browserSignRandHash = 'W2152Sfdxrf3#';
	const browserSignKeyName = '__app_session_fp';
	
	private static $domain = null;
	private static $sessionName;//PHPSESSID
	
	public static function getId() {
		return session_id();
	}
	
	public static function setDomain($domain) {
		self::$domain = $domain;
	}
	
	public static function setName($name) {
		self::$sessionName = $name;
	}
	
	public static function start() {
		if (session_id()) return;
		if (null === self::$domain) {
			self::$domain = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
		}
		ini_set('session.use_trans_sid', false);
		ini_set('session.use_cookies', true);
		ini_set('session.use_only_cookies', true);
		ini_set('session.cookie_domain', self::$domain);
		ini_set('session.gc_maxlifetime', 1000);
		//ini_set('session.save_path', $_SERVER['DOCUMENT_ROOT'] .'../sessions/');
		//self::$sessionName = substr(self::$domain, 0, strpos(self::$domain, '.'));
		if (self::$sessionName) 
			session_name(self::$sessionName);
		session_set_cookie_params(0, '/', self::$domain, (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'), true);
		session_start();
		$browserSign = self::getBrowserSign();
		if (self::get(self::browserSignKeyName) !== $browserSign) {
			self::set(self::browserSignKeyName, $browserSign);
			self::regenerate();
		}
		register_shutdown_function([__CLASS__, 'close']);
	}
	
	public static function regenerate() {
		session_regenerate_id(true);
	}
	
	public static function close() {
		session_write_close();
	}

	public static function destroy() {
		session_unset();
		session_destroy();
		$_SESSION = [];
	}

	public static function clear() {
		self::start();
		$_SESSION = [];
	}
	
	public static function register($name, $value) {
		if (isset($_SESSION[$name]))
			return;
		self::set($name, $value);
	}
	
	public static function set($name, $value) {
		self::start();
		if (null === $value)
			unset($_SESSION[$name]);
		else
			$_SESSION[$name] = $value;
	}
	
	public static function get($name) {
		return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
	}

	private static function getBrowserSign() {
		return (isset($_SERVER['HTTP_USER_AGENT']) ? MD5(self::browserSignRandHash . ':' . $_SERVER['HTTP_USER_AGENT']) : null);
	}
	
}