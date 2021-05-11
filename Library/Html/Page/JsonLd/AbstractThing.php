<?php

namespace Html\Page\JsonLd;

abstract class AbstractThing {

	const SCHEME = 'https://schema.org';

	protected $type;
	protected $data = [];

	public function __construct(array $data = []) {
		$this->type = str_replace(__NAMESPACE__ . '\\', '', get_class($this));
		$this->init();
		foreach ($data as $k => $v)
			$this->$k = $v;
	}

	public function __set($name, $value) {
		$this->data[$name] = $value;
	}

	public function __get($name) {
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}

	public function getHtml() {
		$data = [
			'@context' => self::SCHEME,
			'@type' => $this->type
		];

		foreach ($this->data as $k => $v) {
			if (empty($v))
				continue;
			$data[$k] = $v;
		}

		return json_encode($data);
	}

	abstract protected function init();

}