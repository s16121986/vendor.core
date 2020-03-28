<?php
namespace Form\Element;

use Format;

class Date extends Text{

	protected $_options = array(
		'inputType' => 'text',
		'maxValue' => null,
		'minValue' => null,
		'format' => 'd.m.Y',
		'autocomplete' => 'off',
		'emptyValue' => false
	);

	protected function prepareValue($value) {
		if ($value) {
			if (is_numeric($value)) {
				$t = $value;
			} else {
				$t = strtotime($value);
			}
			if ($t > 0) {
				$value = date('Y-m-d', $t);
				if ($this->maxValue && ($value > $this->maxValue)) {
					return null;
				}
				if ($this->minValue && ($value < $this->minValue)) {
					return null;
				}
				return $value;
			}
		} elseif (false !== $this->emptyValue && $this->emptyValue === $value) {
			return $value;
		}
		return null;
	}

	public function getHtml() {
		$d = '';
		if ($this->getValue()) {
			$t = strtotime($this->prepareValue($this->getValue()));
			if ($t > 0) {
				$d = Format::formatDate($t);
			}
		}
		$s = '<input type="' . $this->inputType . '"' . $this->attrToString() . ' value="' . $d . '" />';
		if (false !== $this->datepicker) {
			$jsOptions = array();
			foreach ($this->_options as $k => $v) {
				if (null !== $v) {
					$jsOptions[$k] = (string)$v;
				}
			}
			$s .= $this->getJs($jsOptions);
		}
		return $s;
	}

}
