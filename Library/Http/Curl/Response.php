<?php
namespace Http\Curl;

use Exception;

class Response{
	
	private $httpCode;
	private $exception;
	private $content;
	private $data = [];
	
	public function __set($name, $value) {
		$this->data[$name] = $value;
	}
	
	public function __get($name) {
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}
	
	public function setHttpCode($httpCode) {
		$this->httpCode = $httpCode;
	}
	
	public function getHttpCode() {
		return $this->httpCode;
	}
	
	public function setContent($content) {
		$this->content = $content;
	}
	
	public function getContent($format = null) {
		switch ($format) {
			case 'json':
				return json_decode($this->content);
		}
		return $this->content;
	}
	
	public function setException($exception) {
		if (is_string($exception))
			$exception = new Exception($exception);
		$this->exception = $exception;
	}
	
	public function getException() {
		return $this->exception;
	}
	
	public function hasException() {
		return (bool)$this->exception;
	}
	
}