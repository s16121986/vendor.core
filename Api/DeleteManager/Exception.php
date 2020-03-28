<?php
namespace Api\DeleteManager;

require_once 'Api/Exception.php';

class Exception extends \Api\Exception{

	public function  __construct($error, $data = null, $previous = null) {
		parent::__construct($error, $data, $previous);
	}

}