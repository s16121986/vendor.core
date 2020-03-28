<?php
namespace Form\Action;

class Submit{

	protected $_options = array();

	protected $_form;

	public function __construct(\Form $form, $options = array()) {
		$this->_form = $form;
		$this->_options = $options;
	}

	public function __get($name) {
		return (isset($this->_options[$name]) ? $this->_options[$name] : null);
	}

	public function submit() {
		$return = false;
		$form = $this->_form;
		if ($form->isSent()) {
			$form->setSubmitted(true);
			$sentData = $this->getSentData();
			if ($form->hasUpload()) {
				$uploadData = $this->getUploadData();
			}
			$return = true;
			foreach ($form->getElements() as $element) {
				if ($element->disabled) {
					continue;
				}
				if ($element->isFileUpload()) {
					if (isset($uploadData[$element->name])) {
						$element->setValue($uploadData[$element->name]);
					}
					if (isset($sentData[$element->name])) {
						$element->setData($sentData[$element->name]);
					}
				} elseif ($element->isSubmittable()) {
					$element->setValue(self::getElementValue($sentData, $element));
				}
				if ($return && !$element->isValid()) {
					$return = false;
				}
			}
		}
		return $return;
	}

	protected function getUploadData() {
		$form = $this->_form;
		$files = $_FILES;
		$data = array();
		if ($form->name) {
			$files = (isset($files[$form->name]) ? $files[$form->name] : array());
		}
		if (isset($files['tmp_name'])) {
			foreach ($files as $paramName => $v) {
				foreach ($v as $fieldName => $value) {
					if (is_array($value)) {
						foreach ($value as $i => $vv) {
							$data[$fieldName][$i][$paramName] = $vv;
						}
					} else {
						$data[$fieldName][$paramName] = $value;
					}
				}
			}
			$dataTemp = $data;
			$data = array();
			foreach ($dataTemp as $fieldName => $items) {
				if (isset($items['tmp_name'])) {
					if ($items['tmp_name'] && $items['error'] == 0) {
						$data[$fieldName] = $items;
					}
				} else {
					foreach ($items as $item) {
						if ($item['tmp_name'] && $item['error'] == 0) {
							$data[$fieldName][] = $item;
						}
					}
				}
			}
		} else {
			foreach ($files as $fieldName => $v) {
				if ($v['tmp_name']) {
					$data[$fieldName] = array();
					if (is_array($v['tmp_name'])) {
						foreach ($v['tmp_name'] as $i => $tmp_name) {
							if ($v['error'][$i] != 0) {
								continue;
							}
							$data[$fieldName][$i] = array();
							foreach ($v as $paramName => $values) {
								$data[$fieldName][$i][$paramName] = $values[$i];
							}
						}
					} else {
						if ($v['error'] != 0) {
							continue;
						}
						foreach ($v as $paramName => $value) {
							$data[$fieldName][$paramName] = $value;
						}
					}
				}
			}
		}
		return $data;
	}

	protected function getSentData() {
		$data = array();
		$form = $this->_form;
		switch ($form->method) {
			case 'POST':$data = $_POST;break;
			case 'GET':$data = $_GET;break;
			default:
				$data = $_REQUEST;
		}
		if ($form->getName()) {
			return (isset($data[$form->getName()]) ? $data[$form->getName()] : array());
		}
		return $data;
	}
	
	private static function getElementValue($data, $element) {
		$name = $element->name;
		/*$name = $element->getInputName();
		preg_match('/(?:\[([a-z0-9_]+)\])/', $name, $matches);
		if ($matches) {
			$tmp = $data;
			foreach ($matches[1] as $k) {
				if (isset($tmp[$k])) {
					$tmp = $tmp[$k];
				} else {
					$tmp = null;
					break;
				}
			}
			return $tmp;
		}*/
		return (isset($data[$name]) ? $data[$name] : null);
	}

}