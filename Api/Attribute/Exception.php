<?php
namespace Api\Attribute;

use Api\Attribute\AbstractAttribute;
use Api\Exception as ApiException;

class Exception extends ApiException{
	
	private $attribute;

	public function  __construct(AbstractAttribute $attribute, string $message = "", int $code = 0, $data = null) {
		$this->attribute = $attribute;
		parent::__construct($message, $code, $data);
	}

	public function getAttribute() {
		return $this->attribute;
	}

}