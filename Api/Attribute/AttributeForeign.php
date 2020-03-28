<?php
namespace Api\Attribute;

class AttributeForeign extends AbstractAttribute{

	protected $qualifiers = [
		'api' => ''
	];

	private function initApi($id) {
		if (null === $this->_api) {
			$api = Loader::loadApi($this->api);
			if ($api->findById($id)) {
				$this->_api = $api;
			} else {
				$this->_api = false;
			}
			unset($api);
		}
		return $this->_api;
	}

	public function getApi($id) {
		return $this->initApi($id);
	}

	public function getData($value) {
		if ($this->initApi($value)) {
			return $this->_api->getData();
		}
	}

	public function checkValue($value) {
		if (parent::checkValue($value) && $this->initApi($value)) {
			return true;
		}
		return false;
	}

	public function prepareValue($value) {
		$value = (int)$value;
		if ($value <= 0) {
			return null;
		}
		return $value;
	}

}