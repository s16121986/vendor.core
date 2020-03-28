<?php
namespace Api;

class Exception extends \Exception{
	
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
	const ID_INCORRECT = 204;
	const ID_EMPTY = 205;
	const FILE_OPEN = 300;
	const FILES_EMPTY = 301;
	const SERVER_NOT_FOUND = 302;

	protected $_error = self::UNKNOWN;
	protected $_data = array();
	
	protected static function _getConstatnts() {
		$cls = get_called_class();
		$refl = new \ReflectionClass($cls);
		$const = $refl->getConstants();
		return $const;
	}
	
	public static function getErrorKey($value) {
		foreach (self::_getConstatnts() as $k => $v) {
			if ($v == $value) {
				return $k;
			}
		}
		return null;
	}

	public function  __construct($error = self::UNKNOWN, $data = null, $previous = null) {
		if (is_int($error)) {
			$this->_error = $error;
			$message = self::getErrorKey($error);
		} else {
			$message = $error;
			$error = self::UNKNOWN;
		}
		if (is_array($data)) {
			if (isset($data['message'])) {
				$message = $data['message'];
				unset($data['message']);
			}
			$this->_data = $data;
		} elseif (null !== $data) {
			$error = $data;
		}
		parent::__construct($message, null, $previous);
	}

	public function __get($name) {
		return (isset($this->_data[$name]) ? $this->_data[$name] : null);
	}

	public function getData() {
		return $this->_data;
	}

}