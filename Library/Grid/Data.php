<?php
namespace Grid;

use Api;

class Data{
	
	protected $api;
	protected $data = null;
	protected $paginator;
	protected $params = array(
		'orderby' => null,
		'sortorder' => 'asc'
	);
	
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
		} elseif (is_array($data)) {
			$this->data = $data;
		}
		return $this;
	}
	
	public function get() {
		if (null === $this->data) {
			$this->load();
		}
		return $this->data;
	}
	
	public function load($params = []) {
		$q = $_GET;
		foreach (['orderby', 'sortorder'] as $k) {
			if (isset($q[$k]) && $q[$k]) {
				$params[$k] = $q[$k];
			}			
		}
		$this->setParams($params);
		$params = $this->getParams();
		if ($this->paginator) {
			$this->paginator->setCount($this->count($params));
			$params = $this->getParams();
		}
		$this->set($this->api->select($params));
		return $this->data;
	}
	
	public function count() {
		return $this->api->count($this->getParams());
	}
	
	public function order($name, $direction = 'asc') {
		return $this->setParams(array(
			'orderby' => $name,
			'sortorder' => $direction
		));
	}
	
	public function isEmpty() {
		$this->get();
		return empty($this->data);
	}
	
}