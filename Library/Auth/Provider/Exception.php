<?php
namespace Auth\Provider;

use Exception as AbstractException;
use Auth\Provider\Storage;

class Exception extends AbstractException{
	
	public function __construct($message = "", $code = 0, AbstractException $previous = null) {
		//Storage::clear();
		return parent::__construct($message, $code, $previous);
	}
	
}