<?php
namespace Auth\Storage;

use Auth\User;
use Auth\Util;
use Http\Session as HttpSession;
use Exception;

class Session extends AbstractStorage{

	const MEMBER = 'Auth';
	const REGENERATE_LIFETIME = 60;
	
	public function __construct(User $user) {
		HttpSession::start();
		return parent::__construct($user);
	}

	public function hasIdentity() {
		return (isset($_SESSION[self::MEMBER])
			&& is_array($_SESSION[self::MEMBER])
			&& isset($_SESSION[self::MEMBER]['user_agent'])
			&& $_SESSION[self::MEMBER]['user_ip'] === Util::getClientIp()
			&& $_SESSION[self::MEMBER]['user_agent'] === Util::getUserAgent()
		);
	}

	public function setIdentity($identity, $redirect = null) {
		HttpSession::set(self::MEMBER, [
			'identity' => $identity,
			'time' => time(),
			'user_ip' => Util::getClientIp(),
			'user_agent' => Util::getUserAgent()
		]);
		return parent::setIdentity($identity, $redirect);
	}

	public function getIdentity() {
		if ($this->hasIdentity()) {
			$time = time();
			if ($time > $_SESSION[self::MEMBER]['time'] + self::REGENERATE_LIFETIME) {
				HttpSession::regenerate(true);
				$_SESSION[self::MEMBER]['time'] = $time;
			}
			return $_SESSION[self::MEMBER]['identity'];
		}
		return null;
	}

	public function clear($params = null, $redirect = null) {
		HttpSession::set(self::MEMBER, null);
		return parent::clear($params, $redirect);
	}

}