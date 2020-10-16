<?php

namespace Grid;

use Api;

class Data {

	protected $api;
	protected $data = null;
	protected $paginator;
	protected $params = [
		'orderby' => null,
		'sortorder' => 'asc'
	];

	public function __construct($data = null) {
		$this->set($data);
	}

	public function __get($name) {
		switch ($name) {
			case 'paginator':
				return $this->paginator;
		}
		return (isset($this->params[$name]) ? $this->params[$name] : null);
	}

	public function setPaginator($paginator) {
		$this->paginator = $paginator;
		return $this;
	}

	public function setParams($params) {
		foreach ($params as $k => $v) {
			$this->params[$k] = $v;
		}
	}

	public function getParams() {
		$params = $this->params;
		if ($this->paginator && $this->paginator->getCount()) {
			$params['start-index'] = $this->paginator->getStartIndex();
			$params['max-results'] = $this->paginator->step;
		}
		return $params;
	}

	public function set($data) {
		if ($data instanceof Api) {
			$this->api = $data;
		} else if (is_iterable($data)) {
			$this->data = $data;
		}
		return $this;
	}

	public function get() {
		if (null !== $this->data)
			return $this->data;
		else if ($this->api)
			return $this->load();
		else
			return [];
	}

	public function load($params = []) {
		$q = $_GET;
		foreach (['orderby', 'sortorder'] as $k) {
			if (isset($q[$k]) && $q[$k]) {
				$params[$k] = $q[$k];
			}
		}
		$this->setParams($params);
		if ($this->paginator) {
			$this->paginator->setCount($this->count());
		}
		$params = $this->getParams();
		$this->set($this->api->select($params)->getItems());
		return $this->data;
	}

	public function count() {
		if ($this->api)
			return $this->api->count($this->getParams());
		else if (is_countable($this->data))
			return count($this->data);
		else
			return 0;
	}

	public function order($name, $direction = 'asc') {
		return $this->setParams([
			'orderby' => $name,
			'sortorder' => $direction
		]);
	}

	public function isEmpty() {
		$this->get();
		return empty($this->data);
	}

}