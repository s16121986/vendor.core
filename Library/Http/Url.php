<?php
namespace Http;

class Url {
	
    const URI_DELIMITER = '/';

	const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';
	const CHAR_GEN_DELIMS = ':\/\?#\[\]@';
	const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';
	const CHAR_RESERVED = ':\/\?#\[\]@!\$&\'\(\)\*\+,;=';

	const SCHEME_HTTP = 'http';
	const SCHEME_HTTPS = 'https';
	
	private $aliases = [];

	protected $params = [
		'scheme' => null,
		'host' => null,
		'port' => null,
		'path' => null,
		'query' => null,
		'hash' => null
	];
	
	public static function parseScheme($uriString) {
		if (!is_string($uriString)) {
			throw new Exception(sprintf(
				'Expecting a string, got %s', (is_object($uriString) ? get_class($uriString) : gettype($uriString))
			));
		}
		if (preg_match('/^([A-Za-z][A-Za-z0-9\.\+\-]*):/', $uriString, $match)) {
			return $match[1];
		}

		return null;
	}
	
	public static function factory($url = null) {
		return new self($url);
	}

	public function __construct($uri = null) {
		$this->init();
		$this->parse($uri ?: 'current');
	}

	public function __get($name) {
		switch ($name) {
			case 'fragment':$name = 'hash';break;
		}
		return (isset($this->params[$name]) ? $this->params[$name] : null);
	}

	public function __set($name, $value) {
		switch ($name) {
			case 'query':
				if (is_array($value)) {
					$value = http_build_query($value);
				} elseif (true === $value) {
					$value = $this->getQuery();
					return;
				}
				break;
		}
		$this->params[$name] = $value;
	}
	
	protected function init() {
		$this
			->setAlias('current', $this->getServer('REQUEST_URI'))
			->setAlias('referer', $this->getServer('HTTP_REFERER'));
	}
	
	public function setAlias($name, $alias) {
		$this->aliases[$name] = $alias;
		return $this;
	}
	
	public function parse($uri) {
		if (null === $uri)
			return $this;
		
		if (is_string($uri)) {
			$uri = str_replace(array_keys($this->aliases), array_values($this->aliases), $uri);
		} elseif (is_array($uri)) {
			$params = $uri;
			if (isset($params['url']) && $params['url']) {
				$this->parse($params['url']);
			}
			unset($params['url']);
			$this->setParams($params);
			return $this;
		}
		
		if (($scheme = self::parseScheme($uri)) !== null) {
			$this->scheme = $scheme;
			$uri = substr($uri, strlen($scheme));
		}

		// Capture authority part
		if (preg_match('|^://([^/\?#]*)|', $uri, $match)) {
			$authority = $match[1];
			$uri = substr($uri, strlen($match[0]));

			// Split authority into userInfo and host
			if (strpos($authority, '@') !== false) {
				// The userInfo can also contain '@' symbols; split $authority
				// into segments, and set it to the last segment.
				$segments = explode('@', $authority);
				$authority = array_pop($segments);
				$userInfo = implode('@', $segments);
				unset($segments);
				//$this->setUserInfo($userInfo);
			}

			$nMatches = preg_match('/:[\d]{1,5}$/', $authority, $matches);
			if ($nMatches === 1) {
				$portLength = strlen($matches[0]);
				$port = substr($matches[0], 1);

				$this->port = (int)$port;
				$authority = substr($authority, 0, -$portLength);
			}

			$this->host = $authority;
		}

		if (!$uri)
			return $this;
		
		// Capture the path
		if (preg_match('|^[^\?#]*|', $uri, $match)) {
			$this->path = $match[0];
			$uri = substr($uri, strlen($match[0]));
		}

		if (!$uri)
			return $this;

		// Capture the query
		if (preg_match('|^\?([^#]*)|', $uri, $match)) {
			$this->query = $match[1];
			$uri = substr($uri, strlen($match[0]));
		}
		if (!$uri)
			return $this;

		// All that's left is the fragment
		if ($uri && substr($uri, 0, 1) == '#') {
			$this->hash = substr($uri, 1);
		}
		
		return $this;
	}
	
	public function setParams($params) {
		foreach ($params as $k => $v) {
			$this->$k = $v;
		}
		return $this;
	}
	
	public function getServer($key) {
		return (isset($_SERVER[$key]) ? $_SERVER[$key] : null);
	}
	
	public function getSiteUrl() {
		return $this->getScheme() . '://' . $this->getHttpHost();
	}

	public function getScheme() {
		if (null === $this->scheme) {
			$this->scheme = ($this->getServer('HTTPS') == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
		}
		return $this->scheme;
	}
	
	public function getPort() {
		if (null === $this->port) {
			$this->port = $this->getServer('SERVER_PORT');
		}
		return $this->port;
	}

	public function getHttpHost() {
		if (null === $this->host) {
			$host = $this->getServer('HTTP_HOST');
			if (empty($host)) {
				$this->host = $this->getServer('SERVER_NAME');
			} else {
				$params = explode(':', $host);
				$this->host = $params[0];
				/*if (isset($params[1])) {
					$this->port = $params[1];
				}*/
			}
		}
		$scheme = $this->getScheme();
		$port = $this->getPort();
		if (($scheme == self::SCHEME_HTTP && $port == 80) || ($scheme == self::SCHEME_HTTPS && $port == 443)) {
			return $this->host;
		} else {
			return $this->host . ':' . $this->port;
		}
	}
	
	public function getPath() {
		if (null === $this->path) {
			$path = $this->getServer('REQUEST_URI');
			if (($p = strpos($path, '?'))) {
				$path = substr($path, 0, $p);
			}
			$this->path = $path;
		}
		return $this->path;
	}
	
	public function getHome() {
		$uri = '';
		if ($this->scheme) {
			$uri .= $this->scheme . ':';
		}
		if ($this->host !== null) {
			$uri .= '//';
			if ($this->userInfo) {
				$uri .= $this->userInfo . '@';
			}
			$uri .= $this->host;
			if ($this->port) {
				//$uri .= ':' . $this->port;
			}
		}
		$uri .= self::URI_DELIMITER;
		return $uri;
	}
	
	public function getQuery() {
		if (null === $this->query) {
			$this->__set('query', $_GET);
		}
		return $this->query;
	}
	
	public function isValid() {
		return true;
	}
	
	public function isAbsolute() {
		return (bool)($this->scheme);
	}
	
	public function toString() {
		if ($this->absolute) {
			$this->getScheme();
			$this->getHttpHost();
		}
		$uri = $this->getHome();
		if ($this->path) {
			$uri .= ltrim($this->path, self::URI_DELIMITER);// . self::URI_DELIMITER;
		}
		if ($this->query) {
			$uri .= "?" . $this->query;
		}
		if ($this->hash) {
			$uri .= "#" . $this->hash;
		}
		return $uri;
	}
	
	public function __toString() {
		return $this->toString();
	}

}
