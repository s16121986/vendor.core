<?php
namespace Auth;

use Db;
use Auth\User\Token;
use Auth\User\Password;
use Auth\Provider\Profile as AuthProfile;
use Auth\Exception;

class User{
	
	protected $options = array(
		'enableSocial' => false,
		'oauth' => 'user_social_ref'
	);
	protected $storage = null;
	protected $token = null;
	protected $password = null;
	protected $id = null;
	protected $source = null;
	protected $data = null;
	protected $exception = null;

	protected static function checkData($data, $tmp, $strict = false) {
		if (!is_array($data))
			return false;
		foreach ($tmp as $k) {
			if (isset($data[$k])) {
				unset($data[$k]);
				continue;
			}
			return false;
		}
		if ($strict && !empty($data))
			return false;
		return true;
	}
	
	public function __construct($options = null) {
		if (is_array($options)) {
			foreach ($options as $k => $v) {
				$this->__set($k, $v);
			}
		}
	}
	
	public function __set($name, $value) {
		switch ($name) {
			case 'storage':$this->setStorage($value);break;
			case 'source':$this->setSource($value);break;
			case 'password':
				if ($value instanceof Password) {
					$this->password = $value;
				} else {
					$this->password = new Password($value);
				}
				break;
		}
		$this->options[$name] = $value;
	}
	
	public function __get($name) {
		switch ($name) {
			case 'id':
			case 'source':
				return $this->$name;
		}
		if (isset($this->options[$name])) {
			return $this->options[$name];
		}
		return (isset($this->data[$name]) ? $this->data[$name] : null);
	}
	
	public function setSource($source) {
		if (is_callable($source)) {
			$this->source = call_user_func($source);
		} elseif (is_array($source)) {
			$callback = function() use ($source) {
				if (isset($_SERVER['HTTP_HOST']) && isset($source[$_SERVER['HTTP_HOST']])) {
					return $source[$_SERVER['HTTP_HOST']];
				}
				return null;
			};
			$this->source = call_user_func($callback);
		} else {
			$this->source =  $source;
		}
		return $this;
	}

	public function setStorage($storage) {
		if (is_string($storage)) {
			if ($storage === 'Cookie') {
				$storage = 'Cookies';
			}
			$cls = 'Auth\\Storage\\' . $storage;
			//include 'Library/Auth/Storage/' . $storage . '.php';
			//Loader::loadClass($cls);
			$storage = new $cls($this);
		}
		$this->storage = $storage;
		return $this;
	}

	public function getStorage() {
		return $this->storage;
	}
	
	public function getToken() {
		if (null === $this->token) {
			$this->token = new Token($this);
		}
		return $this->token;
	}
	
	public function getPassword() {
		if (null === $this->password) {
			$this->password = new Password('md5');
		}
		return $this->password;
	}
	
	public function getData() {
		return $this->data;
	}
	
	public function setId($userId, $storage = true) {
		$authRow = Db::from('users', '*')
						->where('id=?', $userId)
						->query()->fetchRow();
		if (empty($authRow)) {
			//$this->_result->setCode(\AUTH_RESULT_CODE::INCORRECT_IDENTITY);
		} else {
			$this->id = $authRow['id'];
			$this->data = $authRow;
			$token = $this->getToken()->get(true);
			if ($storage) {
				$this->getStorage()->setIdentity($token);
			}
			$this->initData();
		}
		return $this;
	}
	
	public function login($data) {
		if ($this->isAuthorized())
			return $this;
		
		if ($data instanceof AuthProfile) {
			$data = array(
				'provider' => $data->provider,
				'identifier' => $data->identifier
			);
		} elseif (!(self::checkData($data, array('login', 'password')) || ($this->enableSocial && self::checkData($data, array('provider', 'identifier'))))) {
			$this->exception = new Exception(Exception::INCORRECT_DATA);
			return false;
		}
		if (isset($data['provider'])) {
			$authRow = Db::from($this->oauth, array('id as oauth_id', 'provider', 'identifier'))
					->joinInner('users', 'users.id=' . $this->oauth . '.user_id', array('id', 'status'))
					->where($this->oauth . '.provider=?', $data['provider'])
					->where($this->oauth . '.identifier=?', $data['identifier'])
					->query()->fetchRow();
		} else {
			$authRow = Db::from('users', '*')
						->where('login=?', $data['login'])
						->where('password=?', $this->getPassword()->encrypt($data['password']))
						//->where('status>0')
						->query()->fetchRow();
		}
				
		if (empty($authRow)) {
			$this->exception = new Exception(Exception::USER_NOT_FOUND);
			return false;
		}
		$this->id = $authRow['id'];
		$this->data = $authRow;
		$token = $this->getToken()->get(true);
		$this->getStorage()->setIdentity($token);
		$this->initData();
		$this->token->log(Token::LOG_LOGIN);
		//$this->getStorage()->setResult($result, $data, $redirect);
		return $this;
	}
	
	public function logout() {
		if ($this->isAuthorized()) {
			$this->getStorage()->clear();
			$this->getToken()->log(Token::LOG_LOGOUT);
		}
		$this->id = null;
		$this->data = null;
		return $this;
	}

	public function authentication() {
		if ($this->isAuthorized()) {
			return $this;
		}
		$token = $this->getStorage()->getIdentity();
		if ($this->getToken()->find($token)) {
			$this->id = $this->token->user_id;
			$this->initData();
			return $this;
		}
		return false;
	}

	public function isAuthorized() {
		return (null !== $this->id);
	}
	
	public function isValid() {
		return (null === $this->exception && $this->isAuthorized());
	}
	
	protected function initData() {
		if (!defined('UserId')) {
			define('UserId', (int)$this->id);
		}
		return $this;
	}
	
	
	

	public function ssoLogin($userId) {

		$this->_result->id = $userId;
		//$this->_result->type = $userType;

		$token = $this->initSession(false);

		if ($token) {
			$this->_result->setToken($token);
			$this->_result->setCode(\AUTH_RESULT_CODE::SUCCESS);
		}

		return $this->_result;
	}
	
}