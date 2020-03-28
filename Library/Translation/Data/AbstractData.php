<?php
namespace Translation\Data;

use Translation\Language as LangObj;

abstract class AbstractData{
	
	protected $language;
	protected $path = null;
	
	public function __construct(LangObj $language, $path = null) {
		$this->language = $language;
		$this->path = $path;
	}
	
	protected static function formatValue($value, $path = 'item') {
		switch (true) {
			case $path === null:
				$path = 'item';
				break;
			case $value instanceof AttributeException:
				$value = AttributeException::getErrorKey($value->getCode()) . '_' . $value->attribute;
				$path = 'error';
				break;
			case $value instanceof ApiException:
				$value = ApiException::getErrorKey($value->getCode());
				$path = 'error';
				break;
			case $value instanceof Exception:
				$value = 'error_unknown';
				$path = 'error';
				break;
		}
		return array($value, $path);
	}
	
}