<?php

use Form\Fieldset as AbstractFeildset;

//use Api;

class Form extends AbstractFeildset {

	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_DELETE = 'DELETE';
	const METHOD_PUT = 'PUT';

	protected $_submitted = false;
	protected $_errors = [];
	protected $_api;
	protected $_options = [
		'api' => null,
		'name' => null,
		'baseParams' => [],
		'method' => 'POST',
		'submitAction' => 'submit',
		'successMessage' => false
	];

	public function __construct($options = null) {
		if (is_string($options)) {
			$options = ['name' => $options];
		}
		if (isset($options['api']) && !isset($options['submitAction'])) {
			$options['submitAction'] = 'apiSubmit';
		}
		parent::__construct($options);
		$this->init();
	}

	protected function init() { }

	public function addElement($element, $type = null, array $options = []) {
		parent::addElement($element, $type, $options);
		$this->setSubmitted(false);
		return $this;
	}

	public function setName($method) {
		return $this->setOption('name', $method);
	}

	public function setMethod($method) {
		return $this->setOption('method', $method);
	}

	public function setData($data) {
		if (!$this->isSubmitted()) {
			if ($data instanceof Api)
				$data = $data->getData();
			parent::setData($data);
		}
		return $this;
	}

	public function setSubmitted($flag) {
		$this->_submitted = (bool)$flag;
		return $this;
	}

	public function isSubmitted() {
		return $this->_submitted;
	}

	public function isSent() {
		switch ($this->method) {
			case self::METHOD_POST:
				return ('POST' == $_SERVER['REQUEST_METHOD'] && (!$this->getName() || (isset($_POST[$this->getName()]) || isset($_FILES[$this->getName()]))));
			case self::METHOD_GET:
				$data = ($this->getName() ? (isset($_GET[$this->getName()]) ? $_GET[$this->getName()] : null) : $_GET);
				return !empty($data);//('GET' == $_SERVER['REQUEST_METHOD'] && (!$this->getName() || (isset($_GET[$this->getName()]))));
		}
		return false;
	}

	public function doAction($action, $options = []) {
		$cls = 'Form\\Action\\' . ucfirst($action);
		$action = new $cls($this, $options);
		if ($action->submit())
			return true;

		return false;
	}

	public function submit($options = []) {
		return $this->doAction($this->submitAction, $options);
	}

	public function addError($error) {
		$this->_errors[] = $error;
		return $this;
	}

	public function getErrors() {
		$errors = $this->_errors;
		if ($this->isSent()) {
			foreach ($this->_elements as $element) {
				if (!$element->isValid())
					$errors[$element->name] = $element->getError();
			}
		}
		return $errors;
	}

	public function renderErrors() {
		if (empty($this->_errors)) {
			return '';
		}
		$s = '<div class="form-errors">';
		//$s .= '<div class="label"><span>!</span> Ошибки:</div>';
		$s .= '<ul>';
		foreach ($this->_errors as $v) {
			$s .= '<li>' . $v . '</li>';
		}
		$s .= '</ul>';
		$s .= '</div>';
		return $s;
	}

	public function report() {
		if ($this->successMessage) {
			return '<div class="form-success"><span>!</span>' . $this->successMessage . '</div>';
		} else {
			return $this->renderErrors();
		}
		return '';
	}

	public function reset() {
		foreach ($this->_elements as $element) {
			$element->reset();
		}
		$this->_errors = [];
		$this->_submitted = false;
	}

	public function table() {
		$html = '';
		//if (!$this->_reported)
		//	$s .= $this->report();
		$html .= '<table class="form-table">';
		$elements = func_get_args();
		if (empty($elements)) {
			$elements = array_keys($this->_elements);
		}
		foreach ($elements as $k) {
			if (isset($this->_elements[$k]) && $this->_elements[$k]->render && !$this->_elements[$k]->isRendered()) {

				if (in_array($this->_elements[$k]->type, ['hidden'])) {
					$html .= $this->_elements[$k]->render();
					continue;
				}
				$html .= '<tr>';
				$html .= '<td class="form-label">' . $this->_elements[$k]->renderLabel() . '</td>';
				$html .= '<td class="form-input">' . $this->_elements[$k]->render() . '</td>';
				$html .= '</tr>';
			}
		}
		$html .= '</table>';
		return $html;
	}

	public function addAttribute($attribute) {
		$systemFields = ['id', 'created', 'updated'];
		if ($attribute->hidden || in_array($attribute->name, $systemFields)) {
			return;
		}
		$options = [
			'label' => lang(($this->api ? strtolower($this->api->getModelName()) : 'attribute') . '_' . $attribute->name),
			'required' => $attribute->required,
			'default' => $attribute->default
		];
		$attributeType = strtolower(str_replace('Api\\Attribute\\', '', get_class($attribute)));
		switch ($attributeType) {
			case AttributeType::Boolean:
				$type = 'checkbox';
				break;
			case AttributeType::Date:
				$type = 'date';
				break;
			case AttributeType::Enum:
				$type = 'enum';
				$options['enum'] = $attribute->enum;
				if (false === $attribute->notnull) {
					$options['emptyItem'] = '';
				}
				break;
			case AttributeType::Number:
				$type = 'number';
				$options['fractionDigits'] = $attribute->fractionDigits;
				break;
			case AttributeType::String:
				$type = 'text';
				break;
			case AttributeType::Year:
				$type = 'year';
				if (false === $attribute->notnull) {
					$options['emptyItem'] = '';
				}
				$options['yearRange'] = $attribute->yearRange;
				break;
			default:
				return;
				$type = 'text';
		}
		$this->addElement($attribute->name, $type, $options);
		return $this;
	}

	public function setApi(Api $api) {
		$attributes = func_get_args();
		array_shift($attributes);
		$this->_options['api'] = $api;
		if (empty($attributes) || $attributes[0]) {
			foreach ($api->getAttributes() as $attribute) {
				if (!isset($this->_elements[$attribute->name]) && (empty($attributes) || in_array($attribute->name, $attributes))) {
					$this->addAttribute($attribute);
				}
			}
		}
		$this->setData($api->getData());
		return $this;
	}

	public function submitApi($options = []) {
		return $this->doAction('apiSubmit', $options);
	}

	public function bind($action, $callback, $params = []) {
		EventManager::bind($this, $action, $callback, $params);
	}

}