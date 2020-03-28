<?php
namespace Api\Attribute;

class AttributeNumber extends AbstractAttribute{

	protected $qualifiers = [
		'digits' => 11,
		'fractionDigits' => 0,
		'nonnegative' => true,
		'allowZero' => true,
		'zero' => true
	];

	public function prepareValue($value) {
		if (null === $value)
			return $value;
		
		if (is_string($value)) {
			$value = str_replace(' ', '', $value);
			$value = str_replace(',', '.', $value);
		}
		$isNegative = (0 > $value);
		if ($isNegative) {
			$value = -$value;
		}
		$value = (string)$value;
		if ($this->fractionDigits) {
			$value = explode('.', str_replace(',', '.', (string)$value));
			if (!isset($value[1])) {
				$value[1] = '0';
			}
			$value = substr($value[0], 0, $this->digits) . '.' . substr($value[1], 0, $this->fractionDigits);
			$value = (float)$value;
		} else {
			$value = substr($value, 0, $this->digits);
			$value = (int)$value;
		}
		if ($isNegative && !$this->nonnegative) {			
			$value = -$value;
		}

		if ((!$this->allowZero || !$this->zero) && $value == 0)
			$value = null;
		
		return $value;
	}

}