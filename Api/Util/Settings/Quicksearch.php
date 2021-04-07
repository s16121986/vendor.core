<?php

namespace Api\Util\Settings;

class Quicksearch {

	const BOUND_LEFT = 'left';
	const BOUND_RIGHT = 'right';
	const BOUND_BOTH = 'both';

	private static $languages = [
		'йцукенгшщзхъфывапролджэячсмитьбюЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ',
		'qwertyuiop[]asdfghjkl;\'zxcvbnm,.QWERTYUIOP{}ASDFGHJKL:"ZXCVBNM<>'
	];
	private static $encoding = 'utf-8';
	protected $paramName = 'quicksearch';
	protected $bounds = self::BOUND_BOTH;
	protected $columns = [];
	protected $enabled = false;
	protected $languagesEnabled = true;
	protected $value = null;

	public function enable() {
		$this->enabled = true;
		return $this;
	}

	public function isEnabled() {
		return $this->enabled && !$this->isEmpty() && !empty($this->columns);
	}

	public function isEmpty() {
		return empty($this->value);
	}

	public function setParamName($name) {
		$this->paramName = $name;
		return $this;
	}

	public function getParamName() {
		return $this->paramName;
	}

	public function setLangugesEnabled($flag) {
		$this->languagesEnabled = $flag;
		return $this;
	}

	public function setBounds($bounds) {
		$this->bounds = $bounds;
		return $this;
	}

	public function getBounds() {
		return $this->bounds;
	}

	public function setColumns($columns) {
		$this->columns = $columns;
		return $this;
	}

	public function getColumns() {
		return $this->columns;
	}

	public function setValue($value) {
		$this->value = trim($value);
		return $this;
	}

	public function getValue() {
		return $this->value;
	}

	public function getValues() {
		return $this->languagesEnabled ? self::getSpellVariants($this->value) : [$this->value];
	}

	public function __toString() {
		return (string) $this->value;
	}

	private static function getSpellVariants($term) {
		$l = mb_strlen($term, self::$encoding);
		$str = '';
		for ($i = 0; $i < $l; $i++) {
			$chr = mb_substr($term, $i, 1, self::$encoding);
			if ($chr != ' ') {
				$pos = false;
				foreach (self::$languages as $lk => $lang) {
					$pos = mb_strpos($lang, $chr, 0, self::$encoding);
					if (false !== $pos) {
						$toLang = self::$languages[1 - $lk];
						$chr = mb_substr($toLang, $pos, 1, self::$encoding);
						break;
					}
				}
			}
			$str .= $chr;
		}
		if ($term === $str) {
			return array($term);
		}
		return array($term, $str);
	}

}
