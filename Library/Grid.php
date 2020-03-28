<?php

use Grid\Data;

class Grid{

	protected $options = array(
		'emptyGridText' => '',
		'view' => 'Table',
		'viewConfig' => null,
		'orderUrl' => null,
		'rowCls' => array('self', 'rowCls')
	);
	protected $columns = array();
	protected $data = null;

	public function __construct($options = array()) {
		$this->setOptions($options);
		$this->data = new Data();
		$this->data->setParams($options);
	}

	public function __get($name) {
		if (isset($this->$name)) {
			return $this->$name;
		}
		return (isset($this->options[$name]) ? $this->options[$name] : null);
	}
	
	public function __set($name, $value) {
		$this->options[$name] = $value;
	}

	public function setOptions($options) {
		foreach ($options as $k => $v) {
			$this->setOption($k, $v);
		}
		return $this;
	}

	public function setOption($key, $option) {
		$this->options[$key] = $option;
		return $this;
	}

	public function addColumn($column, $type = null, array $options = []) {
		if (is_array($type)) {
			$options = $type;
			$type = 'text';
		}
		if (is_string($column)) {
			$cls = 'Grid\\Column\\' . ucfirst($type);
			/*if (!class_exists($cls)) {
				include 'Library/' . str_replace('_', '/', $cls) . '.php';
			}*/
			$column = new $cls($column, $options);
		} elseif ($column instanceof Grid\Column) {

		} else {

		}
		$this->columns[$column->name] = $column;
		return $this;
	}
	
	public function getColumns() {
		return $this->columns;
	}
	
	public function getColumn($name) {
		return (isset($this->columns[$name]) ? $this->columns[$name] : null);
	}

	public function setRows($rows) {
		return $this->setData($rows);
	}
	
	public function setData($data) {
		$this->data->set($data);
		return $this;
	}
	
	public function setParams(array $params) {
		$this->data->setParams($params);
		return $this;
	}
	
	public function getData() {
		return $this->data;
	}
	
	public function setForm(Form $form) {
		$this->form = $form;
		return $this;
	}
	
	public function setPaginator($paginator) {
		$this->data->setPaginator($paginator);
		return $this;
	}
	
	public function getPaginator() {
		return $this->data->paginator;
	}
	
	public function isEmpty() {
		return $this->data->isEmpty();
	}

	public function render() {
		$cls = 'Grid\View\\' . $this->view;
		$view = new $cls($this);
		return $view->render();
	}
	
	public function getOrder() {
		return $this->data->getParams();
	}

}