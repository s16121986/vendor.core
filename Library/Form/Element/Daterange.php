<?php
namespace Form\Element;

use Format;
use Stdlib\DateTime;

class Daterange extends Text{
	
	const delimeter = ' - ';

	protected $_options = array(
		'inputType' => 'text',
		'maxValue' => null,
		'minValue' => null,
		'format' => 'd.m.Y',
		'autocomplete' => 'off',
		'emptyValue' => false
	);

	protected function prepareValue($value) {
		if (is_string($value) && $value) {
			$dates = explode(self::delimeter, $value);
			return [
				'valueFrom' => $dates[0] ? DateTime::serverDate($dates[0]) : null,
				'valueTo' => (isset($dates[1]) && $dates[1]) ? DateTime::serverDate($dates[1]) : null
			];
		}
		return null;
	}

	public function getHtml() {
		$d = '';
		if ($this->getValue()) {
			$a = [];
			foreach ($this->getValue() as $date) {
				if ($date)
					$a[] = DateTime::factory($date)->format('date');
			}
			$d = implode(self::delimeter, $a);
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
