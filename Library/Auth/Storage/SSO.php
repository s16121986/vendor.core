<?php
namespace Auth\Storage;

class SSO extends Session{

	protected $_ssoRedirect = true;

	public function setSSORedirect($flag) {
		$this->_ssoRedirect = (bool)$flag;
		return $this;
	}

    protected function getHeader($header) {
        if (empty($header)) {
            //require_once '/Controller/Request/Exception.php';
            throw new Exception('An HTTP header name is required');
        }

        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            /*if (!empty($headers[$header])) {
                return $headers[$header];
            }*/
        }

		$header = strtolower($header);

		foreach ($headers as $k => $v) {
			if (strtolower($k) == $header) {
				return $v;
			}
		}

        return false;
    }

	protected function canRedirect() {
		if ($this->_ssoRedirect) {
			return ($_SERVER['REQUEST_METHOD'] === 'GET' && $this->getHeader('X_REQUESTED_WITH') != 'XMLHttpRequest');
		}
		return false;
	}

	public function hasIdentity() {
		return parent::hasIdentity();
	}

	public function getIdentity() {
		if ($this->hasIdentity()) {
			return parent::getIdentity();
		}
		if (isset($_SESSION[\Auth\SSO::HASH_KEY])) {

		} elseif (isset($_GET[\Auth\SSO::HASH_KEY])) {
			$token = false;
			$hash = $_GET[\Auth\SSO::HASH_KEY];
			if ($hash && isset($_GET[\Auth\SSO::USER_KEY])) {
				$userId = (int)$_GET[\Auth\SSO::USER_KEY];
				$user = Db::from('users', array('id', 'email', 'password'))
						->where('id=?', $userId)
						->query()->fetchRow();
				if (!empty($user)) {
					//check user
					//print_r($user);
					if (\Auth\SSO::hash($user) === $hash) {
						//init new/prev session
						$result = Auth::getInstance()
								->getAction()
								->ssoLogin($user['id'], USER_TYPE::USER);
						if ($result->isValid()) {
							parent::setIdentity($result->getToken());
							$token = true;
						}
					}
				}
			}
			if (!$token) {
				$_SESSION[\Auth\SSO::HASH_KEY] = 0;
			}
			//original url
			$this->_redirectComplete();
		} elseif ($this->canRedirect()) {
			$this->_ssoRedirectGet();
		}
		return null;
	}

	public function setIdentity($identity, $redirect = null) {
		parent::setIdentity($identity);
		$this->_ssoRedirectSet($redirect);
	}

	public function clear($params = null, $redirect = null) {
		unset($_SESSION[\Auth\SSO::HASH_KEY]);
		if (true === $params) {
			$this->_ssoRedirectClear($redirect);
		}
		return parent::clear($params);
	}

	private static function _getRequest($redirect = null) {
		if (null === $redirect) {
			return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		} else {
			return $redirect;
		}
	}

	private function _redirectComplete() {
		$url = self::_getRequest();
		$url = substr($url, 0, strpos($url, '?'));
		$params = $_GET;
		unset($params[\Auth\SSO::HASH_KEY], $params[\Auth\SSO::USER_KEY]);
		if (!empty($params)) {
			$url .= '?' . http_build_query($params);
		}
		unset($params);
		self::_redirect($url);
	}

	private function _ssoRedirectGet() {
		$params = array(
			'url' => self::_getRequest(),
			'action' => 'get'
		);
		self::_redirect('http://' . \Auth\SSO::COOKIE_DOMAIN . '/?' . http_build_query($params));
	}

	private function _ssoRedirectSet($redirect = null) {
		$this->_data['password'] = md5($this->_data['password']);
		$params = array(
			'url' => self::_getRequest($redirect),
			'action' => 'set',
			\Auth\SSO::USER_KEY => $this->_result->id,
			\Auth\SSO::HASH_KEY => \Auth\SSO::hash($this->_data)
		);
		self::_redirect('http://' . \Auth\SSO::COOKIE_DOMAIN . '/?' . http_build_query($params));
	}

	private function _ssoRedirectClear($redirect = null) {
		$params = array(
			'url' => self::_getRequest($redirect),
			'action' => 'clear'
		);
		self::_redirect('http://' . \Auth\SSO::COOKIE_DOMAIN . '/?' . http_build_query($params));
	}

	private static function _redirect($url) {
		header('Location: ' . $url);
		exit;
	}

}