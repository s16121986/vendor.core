<?php
namespace Api\Router;

use Exception;
use Api\Exception as ApiException;

class Result{
	
	const SUCCESS = 1;
	const ERROR = -1;
	
	protected $code = self::SUCCESS;
	protected $message = '';
	protected $error;
	protected $exception;
	protected $data = null;
	
	public function set($name, $value = null) {
		if (null === $value) {
			$this->data = $name;
		} else {
			if (null === $this->data) $this->data = array();
			$this->data[$name] = $value;
		}
		return $this;
	}
	
	public function get() {
		return $this->data;
	}
	
	public function setMessage($message) {
		$this->message = $message;
		return $this;
	}
	
	public function getMessage() {
		return $this->message;
	}
	
	public function setErrorCode($code) {
		$this->error = $code;
		return $this;
	}
	
	public function getErrorCode() {
		return $this->error;
	}
	
	public function setCode($code) {
		$this->code = $code;
		return $this;
	}
	
	public function getCode() {
		return $this->code;
	}

	public function getException() {
		return $this->exception;
	}
	
	public function setException($e) {
		$this->setCode(self::ERROR);
		$this->setErrorCode($e->getCode());
		$this->setMessage($e->getMessage());
		$this->exception = $e;
		if ($e instanceof ApiException) {
			$this->set(array(
				'data' => $e->getData()
			));
		}
		return $this;
	}
	
	public function isValid() {
		return ($this->code > 0);
	}
	
}