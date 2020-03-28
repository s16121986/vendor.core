<?php
namespace Http;

class Request {

	const SCHEME_HTTP  = 'http';
    const SCHEME_HTTPS = 'https';

	public function __get($key) {
		switch (true) {
			case isset($_GET[$key]):
				return $_GET[$key];
			case isset($_POST[$key]):
				return $_POST[$key];
			case isset($_COOKIE[$key]):
				return $_COOKIE[$key];
			case ($key == 'REQUEST_URI'):
				return $this->getRequestUri();
			case ($key == 'PATH_INFO'):
				return $this->getPathInfo();
			case isset($_SERVER[$key]):
				return $_SERVER[$key];
			case isset($_ENV[$key]):
				return $_ENV[$key];
			default:
				return null;
		}
	}

	public function __isset($key) {
		switch (true) {
			case isset($_GET[$key]):
				return true;
			case isset($_POST[$key]):
				return true;
			case isset($_COOKIE[$key]):
				return true;
			case isset($_SERVER[$key]):
				return true;
			case isset($_ENV[$key]):
				return true;
			default:
				return false;
		}
	}

	public function get($key) {
		return $this->__get($key);
	}

	public function has($key) {
		return $this->__isset($key);
	}

	public function getRequestUri() {
		if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // check this first so IIS will catch
			$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif (
		// IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
				isset($_SERVER['IIS_WasUrlRewritten']) && $_SERVER['IIS_WasUrlRewritten'] == '1' && isset($_SERVER['UNENCODED_URL']) && $_SERVER['UNENCODED_URL'] != ''
		) {
			$requestUri = $_SERVER['UNENCODED_URL'];
		} elseif (isset($_SERVER['REQUEST_URI'])) {
			$requestUri = $_SERVER['REQUEST_URI'];
			// Http proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
			$schemeAndHttpHost = $this->getScheme() . '://' . $this->getHttpHost();
			if (strpos($requestUri, $schemeAndHttpHost) === 0) {
				$requestUri = substr($requestUri, strlen($schemeAndHttpHost));
			}
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0, PHP as CGI
			$requestUri = $_SERVER['ORIG_PATH_INFO'];
			if (!empty($_SERVER['QUERY_STRING'])) {
				$requestUri .= '?' . $_SERVER['QUERY_STRING'];
			}
		} else {
			return '';
		}
		return $requestUri;
	}
	
	public function getPath() {
		$requestUri = $this->getRequestUri();
		if ($requestUri && false !== ($pos = strpos($requestUri, '?'))) {
			return substr($requestUri, 0, $pos);
		}
		return $requestUri;
	}

	public function getQuery($key = null, $default = null) {
		if (null === $key) {
			return $_GET;
		}

		return (isset($_GET[$key])) ? $_GET[$key] : $default;
	}

	public function getPost($key = null, $default = null) {
		if (null === $key) {
			return $_POST;
		}

		return (isset($_POST[$key])) ? $_POST[$key] : $default;
	}

	public function getCookie($key = null, $default = null) {
		if (null === $key) {
			return $_COOKIE;
		}

		return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
	}

	public function getServer($key = null, $default = null) {
		if (null === $key) {
			return $_SERVER;
		}

		return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
	}

	public function getEnv($key = null, $default = null) {
		if (null === $key) {
			return $_ENV;
		}
		return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
	}

	public function getMethod() {
		return $this->getServer('REQUEST_METHOD');
	}

	public function isPost() {
		return 'POST' === $this->getMethod();
	}

	public function isGet() {
		return 'GET' === $this->getMethod();
	}

	public function isPut() {
		return 'PUT' === $this->getMethod();
	}

	public function isDelete() {
		return 'DELETE' === $this->getMethod();
	}

	public function isHead() {
		return 'HEAD' === $this->getMethod();
	}

	public function isOptions() {
		return 'OPTIONS' === $this->getMethod();
	}

	public function isXmlHttpRequest() {
		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
	}

	public function isSecure() {
		return ($this->getScheme() === self::SCHEME_HTTPS);
	}

	public function getRawBody() {
		return file_get_contents('php://input');
	}

	public function getHeaders() {
		if (function_exists('apache_request_headers'))
			return apache_request_headers();
		elseif (function_exists('getallheaders'))
			return getallheaders();
		return [];
	}

	public function getHeader($header) {
		// Try to get it from the $_SERVER array first
		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		if (null !== $this->getServer($temp))
			return $this->getServer($temp);
		elseif (null !== $this->getServer('REDIRECT_' . $temp))
			return $this->getServer('REDIRECT_' . $temp);

		$headers = $this->getHeaders();
		$temp = strtolower($header);
		foreach ($headers as $k => $v) {
			if (strtolower($k) === $temp) {
				return $v;
			}
		}

		return null;
	}

	public function getScheme() {
		return ($this->getServer('HTTPS') == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
	}

	public function getHttpHost() {
		$host = $this->getServer('HTTP_HOST');
		if (!empty($host)) {
			return $host;
		}

		$scheme = $this->getScheme();
		$name = $this->getServer('SERVER_NAME');
		$port = $this->getServer('SERVER_PORT');

		if (($scheme == self::SCHEME_HTTP && $port == 80) || ($scheme == self::SCHEME_HTTPS && $port == 443)) {
			return $name;
		} else {
			return $name . ':' . $port;
		}
	}

	public function getUserAgent() {
		return $this->getServer('HTTP_USER_AGENT');
	}

	public function getClientIp($checkProxy = true) {
		if ($checkProxy && $this->getServer('HTTP_CLIENT_IP') !== null) {
			return $this->getServer('HTTP_CLIENT_IP');
		} else if ($checkProxy && $this->getServer('HTTP_X_FORWARDED_FOR') !== null) {
			return $this->getServer('HTTP_X_FORWARDED_FOR');
		} else {
			return $this->getServer('REMOTE_ADDR');
		}
	}

}
