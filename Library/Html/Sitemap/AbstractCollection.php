<?php

namespace Html\Sitemap;

abstract class AbstractCollection extends AbstractXml {

	protected $tag;
	protected $items = [];

	protected function add(AbstractParent $item) {
		$this->items[] = $item;
		return $this;
	}
	
	public function out() {
		header('Content-type: text/xml; charset="UTF-8"');
		echo $this->__toString();
	}

	public function __toString() {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . self::NL;
		$xml = '<' . $this->tag . '>' . self::NL;
		foreach ($this->items as $item) {
			$xml .= (string) $item;
		}
		$xml .= '</' . $this->tag . '>';
		return $xml;
	}

}
