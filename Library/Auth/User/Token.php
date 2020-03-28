<?php
namespace Auth\User;

use Db;
use Auth\User;
use Auth\Util;

class Token{

	const tokenCharset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz123456789_-';
	const LOG_LOGIN = 1;
	const LOG_LOGOUT = 2;
	const LOG_UPDATE = 3;
	
	protected $table = 'auth';
	protected $user;
	protected $token;
	protected $userAgent;
	protected $userIp;
	protected $data = null;
	
	public function __construct(User $user) {
		$this->user = $user;
		$this->userAgent = Util::getUserAgent();
		$this->userIp = Util::getClientIp();
	}
	
	public function __get($name) {
		if (isset($this->$name)) {
			return $this->$name;
		}
		return (isset($this->data[$name]) ? $this->data[$name] : null);
	}
	
	public function getData() {
		return $this->data;
	}

	public function generate($length = 200) {
		$count = strlen(self::tokenCharset) - 1;
		do {
			$token = '';
			for ($i = 0; $i < $length; $i++) {
				$token .= substr(self::tokenCharset, mt_rand(0, $count), 1);
			}
		} while (Db::from($this->table)->where('token=?', $token)->query()->fetchRow());
		$this->token = $token;
		return $token;
	}
	
	public function create() {
		$this->generate();
		$this->data  = array(
			'token' => $this->token,
			'user_id' => $this->user->id,
			'source' => $this->user->source,
			'user_agent' => $this->userAgent,
			'user_ip' => $this->userIp,
			'created' => 'CURRENT_TIMESTAMP'
		);
		Db::insert($this->table, $this->data);
		return $this;
	}
	
	public function get($create = false) {
		$w = implode(' AND ', $this->getAuthParams(true));
		$data = Db::from('auth', array('token', 'user_id', 'source', 'user_agent', 'user_ip', 'created'))
			->where($w)
			->query()->fetchRow();
		if (empty($data)) {
			if ($create) {
				$this->create();
			}
		} else {
			$this->token = $data['token'];
			$this->data = $data;
			Db::query('UPDATE ' . $this->table . ' SET updated=CURRENT_TIMESTAMP WHERE ' . $w);
		}
		return $this->token;
	}
	
	public function find($token) {
		if (empty($token) && !is_string($token)) {
			return false;
		}
		$data = Db::from('auth', array('token', 'user_id', 'source', 'user_agent', 'user_ip', 'created'))
			->where('token=?', $token)
			->query()->fetchRow();
		if ($data) {
			$this->token = $data['token'];
			$this->data = $data;
			Db::query('UPDATE ' . $this->table . ' SET updated=CURRENT_TIMESTAMP WHERE token="' . $this->token . '"');
			return true;
		}
		return false;
	}
	
	public function destroy() {
		Db::delete($this->table, $this->getAuthParams());
		return $this;
	}
	
	public function log($type) {
		Db::insert('auth_log', array(
			'user_id' => $this->user->id,
			'source' => $this->user->source,
			'type' => $type,
			'user_agent' => $this->userAgent,
			'user_ip' => $this->userIp,
			'created' => 'CURRENT_TIMESTAMP'
		));
		return $this;
	}
	
	public function isEmpty() {
		return empty($this->token);
	}

	protected function getAuthParams($sql = false) {
		$params = array(
			'user_id' => $this->user->id,
		);
		if ($this->user->source) {
			$params['source'] = $this->user->source;
		}
		if ($sql) {
			$sqlParams = array();
			foreach ($params as $key => $param) {
				$sqlParams[] = '`' . $key . '`="' . $param . '"';
			}
			return $sqlParams;
		}
		return $params;
	}
	
}