<?php

namespace Api\Router;

class Response extends Result {

	protected $options = [
		'format' => 'json'
	];
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

	public function send() {
		header('HTTP/1.1 ' . $this->httpCode);
		header('Content-Type: application/json');
		foreach ($this->headers as $header) {
			header($header);
		}
		$format = new Response\Json($this);
		echo $format->getContent();
	}

}