<?php
namespace Mvc\Response;

class Json extends Http{
	
	public function __construct(array $data = []) {
		$this->init();
		$this->setData($data);
	}
	
	public function setData($data) {
		return $this->setContent(json_encode($data));
	}
	
	protected function init() {
		$this->setContentType('application/json', 'default');
		parent::init();
	}
	
}