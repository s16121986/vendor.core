<?php

namespace Html\Sitemap\Tag;

use Html\Sitemap\AbstractXml;

class Tag extends AbstractXml {

	protected $value;
	protected $attributes;

	public function __construct($tag, array $attributes = []) {
		$this->tag = $tag;
		$this->attributes = $attributes;
	}

	public function setValue($value) {
		switch ($this->tag) {
			case 'lastmod':
				$value = $value ? substr($value, 0, 10) : null;
				break;
			case 'priority':
				$value = (float)$value;
				if ($value <= 0 || $value > 1)
					$value = null;
				break;
				
		}
		$this->value = $value;
	}
	
	public function isEmpty() {
		return null === $this->value && empty($this->attributes);
	}

	public function __toString() {
		$s = '<' . $this->tag;
		
		foreach ($this->attributes as $k => $v) {
			$s .= ' ' . $k . '="' . $v . '"';
		}
		
		if (null === $this->value)
			$s .= ' />';
		else
			$s .= '>' . self::escape($this->value) . '</' . $this->tag . '>';
		
		return $s;
	}

}
