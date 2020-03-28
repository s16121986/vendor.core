<?php
namespace Form\Element;

use Translation\Calendar;

class Birthday extends Xhtml{

	protected $_options = array(
		'yearRange' => 100
	);

	private $_years;
	private $_months;
	private $_days;

	public function __get($name) {
		if (isset($this->_value[$name])) {
			return $this->_value[$name];
		}
		return parent::__get($name);
	}

	private function getHtmlItem($key, $items) {
		$attr[] = 'name="' . $this->getInputName() . '[' . $key . ']"';
		foreach (array() as $k) {
			if ($this->$k) {
				$attr[] = $k . '="' . $this->$k . '"';
			}
		}
		if ($this->disabled) {
			$attr[] = 'disabled="disabled"';
		}
		$html = '<select class="part-' . $key . '" ' . implode(' ', $attr) . '">'
			. '<option></option>';
		foreach ($items as $val => $text) {
			$isSel = ($val == $this->$key);
			if ($val == 0 && $val !== '') {
				$isSel = ($val === $this->$key);
			}
			$html .= '<option value="' . self::escape($val) . '"' . ($isSel ? ' selected' : '') . '>' . $text . '</option>';
		}
		$html .= '</select>';
		return $html;
	}

	public function getYears() {
		if (null === $this->_years) {
			$this->_years = array();
			$currentYear = now()->getYear();
			for ($i = 0; $i <= $this->yearRange; $i++) {
				$this->_years[$currentYear - $i] = $currentYear - $i;
			}
		}
		return $this->_years;
	}

	public function getMonths() {
		if (null === $this->_months) {
			$this->_months = array();
			foreach (Calendar::getMonths() as $k => $v) {
				$this->_months[$k + 1] = $v;
			}
		}
		return $this->_months;
	}

	public function getDays() {
		if (null === $this->_days) {
			$this->_days = array();
			for ($i = 1; $i <= 31; $i++) {
				$this->_days[$i] = $i;
			}
		}
		return $this->_days;
	}

	protected function prepareValue($value) {
		if (is_string($value) && preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $value)) {
			$valueTemp = explode('-', $value);
			$value = array(
				'year' => (int)$valueTemp[0],
				'month' => (int)$valueTemp[1],
				'day' => (int)$valueTemp[2]
			);
		}
		if (is_array($value)) {
			$valueArr = array();
			foreach (array(
				'year' => 'getYears',
				'month' => 'getMonths',
				'day' => 'getDays'
			) as $key => $itemsMethod) {
				if (!isset($value[$key]) || !array_key_exists($value[$key], $this->$itemsMethod())) {
					return null;
				}
				$valueArr[$key] = $value[$key];
			}
			return $valueArr;
		}
		return null;
	}

	public function getValue() {
		if (($value = parent::getValue()) && is_array($value)) {
			$value['month'] = str_pad($value['month'], 2, '0', STR_PAD_LEFT);
			$value['day'] = str_pad($value['day'], 2, '0', STR_PAD_LEFT);
			return implode('-', $value);
		}
		return null;
	}

	public function getHtml() {
		$html = '';
		$html .= $this->getHtmlItem('day', $this->getDays());
		$html .= $this->getHtmlItem('month', $this->getMonths());
		$html .= $this->getHtmlItem('year', $this->getYears());
		$html .= '';
		return $html;
	}
}
