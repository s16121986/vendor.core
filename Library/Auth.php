<?php
use Auth\User;

class Auth {

	protected static $user;

	public static function factory($options) {
		$user = new User($options);
		if (null === self::$user) {
			self::$user = $user;
		}
		return $user;
	}

	public static function getUser() {
		return self::$user;
	}
	
	public static function logout() {
		return self::getUser()->logout();
	}
	
	public static function login($data) {
		return self::getUser()->login($data);
	}
	
	public static function authentication() {
		return self::getUser()->authentication();
	}
	
	public static function isAuthorized() {
		return (self::$user && self::getUser()->isAuthorized());
	}

}