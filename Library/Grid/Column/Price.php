<?php
namespace Grid\Column;

class Price extends Number{

	protected $_options = array(
		'format' => 'NFD=2;NDS=,;NGS= ',
		'currency' => null,
		'currencyIndex' => null,
		'enum' => 'CURRENCY'
	);

	public function render($value, $row) {
		if (!$value) {
			return '';
		}
		if ($this->currencyIndex) {
			$cv = $row->{$this->currencyIndex};
		} elseif ($this->currency) {
			$cv = $this->currency;
		} else {
			$cv = call_user_func(['\\' . $this->enum, 'getDefault']);
		}
		
		return parent::render($value, $row) 
			. ' <span>' . call_user_func(array('\\' . $this->enum, 'getLabel'), $cv) . '</span>';
	}

}