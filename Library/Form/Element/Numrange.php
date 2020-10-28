<?php
namespace Form\Element;


class Numrange extends Text{
	
	private static function _getValue($value, $assoc) {
		if (!isset($value[$assoc]) || $value[$assoc] === '')
			return null;
		return (int)$value[$assoc];
	}
	
	private function _attr($name, $num, $default = false) {
		if (!$this->$name || !isset($this->$name[$num])) {
			if (false === $default)
				return '';
			
			return ' ' . $name . '="' . $default . '"';
		}
		return ' ' . $name . '="' . $this->$name[$num] . '"';
	}

	public function checkValue($value) {
		return is_array($value);
	}

	protected function prepareValue($value) {
		$valueFrom = self::_getValue($value, 'valueFrom');
		$valueTo = self::_getValue($value, 'valueTo');
		if (null === $valueFrom && null === $valueTo)
			return null;
		
		if (null !== $valueTo && $valueFrom > $valueTo)
			$valueTo = $valueFrom;
		
		return [
			'valueFrom' => $valueFrom,
			'valueTo' => $valueTo
		];
	}

	public function getHtml() {
		$values = $this->getValue();
		$inputName = $this->getInputName();
		$s = '<div class="element-numrange" id="' . $this->getId() . '">';
		$s .= '<input type="number"'
				. ' class="field-number"'
				. ' name="' . $inputName . '[valueFrom]"'
				. $this->_attr('min', 0, 0)
				. $this->_attr('placeholder', 0)
				. ' value="' . ($values ? $values['valueFrom'] : '') . '" />';
		$s .= '<input type="number"'
				. ' class="field-number"'
				. ' name="' . $inputName . '[valueTo]"'
				. $this->_attr('min', 1, 0)
				. $this->_attr('placeholder', 1)
				. ' value="' . ($values ? $values['valueTo'] : '') . '" />';
		$s .= '</div>';
		return $s;
	}

}
