<?php
namespace Api\Attribute;

use Api\Exception as ApiException;

class Exception extends ApiException{

	public function  __construct($error, $data = null, $previous = null) {
		if (is_string($data)) {
			$data = array('attribute' => $data);
		}
		$error = self::getErrorKey($error) . ' (' . $data['attribute'] . ')';
		parent::__construct($error, $data, $previous);
	}

}