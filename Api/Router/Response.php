<?php

namespace Api\Router;

use Exception;

class Response extends Result {

	private static $contentTypes = [
		'json' => 'application/json',
		'xml' => 'application/xml'
	];

	protected $contentType = 'json';
	protected $httpCode = 200;
	protected $headers = [];
	protected $results = [];

	public function addResult(Result $result) {
		$this->results[] = $result;
		return $this;
	}

	public function addHeader($name, $value = null) {
		$this->headers[] = $name . ': ' . $value;
		return $this;
	}

	public function getResults() {
		return $this->results;
	}

	public function setHttpCode($code) {
		$this->httpCode = $code;
		return $this;
	}

	public function setHttpError($code, $message) {
		return $this
			->setCode(self::ERROR)
			->setHttpCode($code)
			->setMessage($message);
	}

	public function setContentType($type) {
		if (!isset(self::$contentTypes[$type])) {
			$type = array_search($type, self::$contentTypes);
			if (false === $type)
				throw new Exception('Content type unsupported');
		}
		$this->contentType = $type;

		return $this;
	}

	public function send() {
		header('HTTP/1.1 ' . $this->httpCode);
		foreach ($this->headers as $header) {
			header($header);
		}
		header('Content-Type: ' . self::$contentTypes[$this->contentType]);
		$cls = __NAMESPACE__ . '\\Response\\' . ucfirst($this->contentType);
		$format = new $cls($this);
		echo $format->getContent();
	}

}