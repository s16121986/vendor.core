<?php
namespace Stdlib;

class Status{
	
	private $status = null;
	private $name = null;
	private $message = null;
	private $messages = array();
	private $data = array();
	
	public function __construct($status = null, $message = null) {
		$this->message = $message;
		$this->status = $status;
	}
	
	public function __set($name, $value) {
		switch ($name) {
			case 'status':
			case 'name':
			case 'message':
				return $this->$name = $value;
		}
		$this->data[$name] = $value;
	}
	
	public function __get($name) {
		switch ($name) {
			case 'status':
			case 'name':
			case 'message':
				return $this->$name;
		}
		if ($name === $this->status) {
			return $this->name;
		}
		return (isset($this->data[$name]) ? $this->data[$name] : null);
	}
	
	public function addMessage($name, $message, $status = null) {
		if (null === $status) $status = $this->status;
		if (!isset($this->messages[$status])) {
			$this->messages[$status] = array();
		}
		$this->messages[$status][$name] = $message;
		return $this;
	}
	
	public function getMessage($name = null, $status = null) {
		if (null === $name) $name = $this->name;
		if (null === $name) {
			return $this->message;
		}
		if (null === $status) $status = $this->status;
		return (isset($this->messages[$status][$name]) ? $this->messages[$status][$name] : '');
	}
	
	public function fromQuery() {
		$data = $_GET;
		if (isset($data['success'])) {
			$this->status = 'success';
			$this->name = $data['success'];
		} elseif (isset($data['error'])) {
			$this->status = 'error';
			$this->name = $data['error'];
		}
	}
	
	public function render() {
		$message = $this->getMessage($this->name, $this->status);
		if ($message) {
			return '<div class="message' . ($this->status ? ' message-' . $this->status : '') . '">' . $message . '</div>';
		}
		return '';
	}
	
	public function __toString() {
		return $this->render();
	}
	
}