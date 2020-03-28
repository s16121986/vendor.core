<?php
namespace Api\Util\Settings;

use Api\Util\Settings\Collection\Item;

class Limit extends Item{

	protected $params = [
		'step' => 0,
		'start' => 0
	];

	public function setStep($step) {
		$step = (int)$step;
		if ($step > 0) {
			$this->_set('step', $step);
		}
		return $this;
	}

	public function setStart($start) {
		$start = (int)$start;
		if ($start > 0) {
			$this->_set('start', $start);
		}
		return $this;
	}

	public function set($step, $start = 0) {
		$this->setStep($step);
		$this->setStart($start);
		return $this;
	}

}