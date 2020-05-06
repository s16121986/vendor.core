<?php

namespace Api\Util;

use Api\Exception;
use Api\EventManager;
use Api\Attribute\AttributeDate;

abstract class BaseApi {

	protected $table = '';
	protected $attributes = [];
	protected $_adapter = null;

	public function __get($name) {
		switch ($name) {
			case 'table':
			case 'attributes':
				return $this->$name;
		}
		if (isset($this->attributes[$name]))
			return $this->attributes[$name]->getValue();
	}

	public function getAttribute($name) {
		return (isset($this->attributes[$name]) ? $this->attributes[$name] : null);
	}

	public function getAttributes() {
		return $this->attributes;
	}

	protected function addAttribute($name, $type = \AttributeType::String, $qualifiers = null) {
		if (!\AttributeType::valueExists($type)) {
			throw new Exception(Exception::UNKNOWN);
		}
		switch ($name) {
			case 'created':
			case 'updated':
				$type = \AttributeType::Date;
				$qualifiers = [
					//'set' => Auth::getUser()->hasRole(USER_ROLE::SYNC),
					//'update' => Auth::getUser()->hasRole(USER_ROLE::SYNC),
					'changeable' => false,
					'dateFractions' => AttributeDate::DateTime
				];
				break;
			case 'deletion_mark':
				$type = \AttributeType::Boolean;
				$qualifiers = ['default' => false];
				break;
		}
		$cls = '\\Api\\Attribute\\Attribute' . ucfirst($type);
		$this->attributes[$name] = new $cls($name, $qualifiers);
		return $this;
	}

	protected function getAdapter() {
		if (!$this->_adapter) {
			$this->_adapter = new \Api\Adapter\Mysql($this);
		}
		return $this->_adapter;
	}

	protected function getSettings($options) {
		$settings = new Settings($options, $this);
		$this->initSettings($settings);
		EventManager::trigger('initSettings', $this, [$settings]);
		$settings->init();

		return $settings;
	}

	protected function initSettings($settings) {
		if ($this->getAttribute('deletion_mark') && !$settings->hasParam('deletion_mark'))
			$settings->filter('`' . $this->table . '`.`deletion_mark`=0');
	}

}
