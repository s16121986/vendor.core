<?php

namespace Grid\Column;

class Url extends AbstractColumn {

	protected $_options = [
		//'href' => '%value%',
		'target' => ''
	];

	protected function init() {
		$this->setOption('href', '%' . $this->name . '%');
	}

}