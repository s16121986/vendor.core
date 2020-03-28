<?php
namespace Auth\Storage;

use Db;
use Exception;
use Http\Cookies as HttpCookies;

class Cookies extends Session{

	const COOKIE_KEY = 'cookie';
	const HASH_KEY = 'hash';
	const USER_KEY = 'user_id';
	const SECRET_KEY = 'm_8gR7Lf3nfT74Ke';
	const COOKIE_AUTH = 'auth';
	const COOKIE_PATH = '/';
	const sp = ':';
	
	private static $cookieDomain = null;

	public function hasIdentity() {
		return parent::hasIdentity();
	}

	public function getIdentity() {
		if ($this->hasIdentity()) {
			return parent::getIdentity();
		}
		$cookie = self::getCookies();
		if ($cookie) {
			$userId = (int)$cookie[self::USER_KEY];
			$user = Db::from('users', array('id', 'login', 'password'))
					->where('id=?', $userId)
					//->where('source' . ($this->user->source ? '=' . $this->user->source : ' IS NULL'))
					->query()->fetchRow();
			if (!empty($user)) {
				$hash = $cookie[self::HASH_KEY];
				$flag = ($this->hash($user) === $hash);
				if ($this->user->enableSocial && !$flag) {
					$q = Db::from($this->user->oauth, array('oauth_id', 'identifier'))
						->where($this->user->oauth . '.user_id=?', $userId)
						->query();
					while ($row = $q->fetch()) {
						if ($this->hash($row) === $hash) {
							$flag = true;
							break;
						}
					}
					$q->getResource()->free();
				}
				if ($flag) {
					//init new/prev session
					//var_dump($user, $cookie);exit;
					if ($this->user->setId($user['id'], false)) {
						parent::setIdentity($this->user->getToken()->token);
						return parent::getIdentity();
					}
				}
			}
		}
		return null;
	}

	public function setIdentity($identity, $redirect = null) {
		parent::setIdentity($identity);
		return self::setCookies($this->user->id, $this->hash($this->user->getData()));
	}
	
	public static function setDomain($domain) {
		self::$cookieDomain = $domain;
	}
	
	public function refresh() {
		return self::setCookies($this->user->id, $this->hash($this->user->getData()));
	}

	public function clear($params = null, $redirect = null) {
		self::clearCookies();
		return parent::clear($params);
	}

	public function hash($data) {
		if (array_key_exists('oauth_id', $data)) {
			$values = array($data['oauth_id'], $data['identifier']);
		} else {
			$values = array($data['login'], $data['password']);
		}
		return hash_hmac('md5', implode('|', $values), self::SECRET_KEY);
	}

	public static function getCookies() {
		if (($cookie = HttpCookies::get(self::COOKIE_AUTH))) {
			$p = explode(self::sp, $cookie);
			if (count($p) == 2) {
				return array(
					self::USER_KEY => $p[0],
					self::HASH_KEY => $p[1]
				);
			}
		}
		return null;
	}

	public static function setCookies($userId, $hash, $remember = true ) {
		$now = CurrentDate();
		$now->modify('+ ' . ($remember ? 15 : 2) . ' days');
		$cookie = implode(self::sp, array($userId, $hash));
		if (!HttpCookies::set(self::COOKIE_AUTH, $cookie, $now->getTimestamp(), '/', self::$cookieDomain)) {
			throw new Exception( "Could not set cookie." );
		}
		return true;
	}

	public static function clearCookies() {
		return HttpCookies::clear(self::COOKIE_AUTH);
    }

}