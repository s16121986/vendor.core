<?php

namespace Exception\Output;

class Html extends System{

	/**
	 * @param Exception $exception
	 * @return string
	 */
	public function format($exception) {
		return $this->_format($exception, true);
	}

}