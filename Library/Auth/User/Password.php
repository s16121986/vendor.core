<?php
namespace Auth\User;

class Password{
	
	const MD5 = 'md5';
	
	protected $method = self::MD5;
	protected $params = null;
	
	public function __construct($method, $params = null) {
		if (is_array($method)) {
			$params = self::_val($method, array('params', 1));
			$method = self::_val($method, array('method', 0));
		}
		$this->setMethod($method);
		$this->setParams($params);
	}
	
	public function setMethod($method) {
		$this->method = $method;
		return $this;
	}
	
	public function setParams($params) {
		$this->params = $params;
		return $this;
	}

	public function encrypt($password) {
		return call_user_func_array(array('self', 'encrypt_' . $this->method), array($password, $this->params));
	}
	
	private static function encrypt_md5($password, $salt = null) {
		return md5($password . $salt);
	}
	
	private static function _val($array, $keys, $default = null) {
		foreach ($keys as $k) {
			if (array_key_exists($k, $array)) {
				return $array[$k];
			}
		}
		return $default;
	}
	
}