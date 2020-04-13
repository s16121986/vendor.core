<?php
namespace Api;

use Exception as BaseException;

class Exception extends BaseException{
	
	const UNKNOWN = 0;
	const FATAL = -1;
	const ACCESS_DENIED = 1;
	const ACTION_UNDEFINED = 2;
	const ACTION_OPTION_REQUIRED = 3;
	const ATTRIBUTE_REQUIRED = 100;
	const ATTRIBUTE_PREDEFINED = 101;
	const ATTRIBUTE_UNDEFINED = 102;
	const ATTRIBUTE_REWRITE = 103;
	const ATTRIBUTE_NOT_CHANGEABLE = 104;
	const ATTRIBUTE_INVALID = 105;
	//const ATTRIBUTE_FOREIGN_KEY_INVALID = 106;
	const DATA_EMPTY = 200;
	const DATA_INCORRECT = 201;
	const RECORD_EMPTY = 202;
	const DELETE_ABORTED = 203;
	const DELETE_RESTRICTED = 204;
	const ID_INCORRECT = 205;
	const ID_EMPTY = 206;
	const FILE_OPEN = 300;
	const FILES_EMPTY = 301;
	const SERVER_NOT_FOUND = 302;

	protected $data = [];

	public function  __construct(string $message = "", int $code = 0, $data = NULL) {
		$this->data = $data;			
		parent::__construct($message, $code);
	}

	public function __get($name) {
		return (isset($this->data[$name]) ? $this->data[$name] : null);
	}

	public function getData() {
		return $this->data;
	}

}