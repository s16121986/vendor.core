<?php
namespace Html\Page;

class Page{
	
	protected $head;
	protected $jsonLd;
	protected $H1;
	protected $text;
	protected $data = array();
	
	public function __construct() {
		$this->head = new Head();
	}
	
	public function __get($name) {
		switch ($name) {
			case 'h1': return $this->H1;
		}
		if (isset($this->$name))
			return $this->$name;
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}
		return null;
		//return $this->getHead()->getMetaContent($name);
	}
	
	public function __set($name, $value) {
		switch ($name) {
			case 'title':
			case 'page_title':
				$this->getHead()->setTitle($value);
				break;
			case 'keywords':
			case 'page_keywords':
				$this->getHead()->addMetaName('keywords', $value);
				break;
			case 'description':
			case 'page_description':
				$this->getHead()->addMetaName('description', $value);
				break;
			default:
				$this->data[$name] = $value;
		}
	}
	
	public function setData($data) {
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$this->$k = $v;
			}
		}
		return $this;
	}
	
	public function getHead() {
		return $this->head;
	}
	
	public function getJsonLd() {
		if (!$this->jsonLd)
			$this->jsonLd = new JsonLd();
		return $this->jsonLd;
	}

	public function setH1($h1) {
		$this->H1 = $h1;
		return $this;
	}

	public function setTitle($title) {
		$this->setH1($title);
		if (!$this->head->getTitle())
			$this->head->setTitle($title);
		return $this;
	}

	public function setText($text) {
		$this->text = $text;
		return $this;
	}
	
	public function doctype() {
		//<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		return '<!DOCTYPE html>';
	}
	
	public function html() {
		return '<html>';
	}

	public function h1() {
		return '<h1>' . ($this->H1 ? $this->H1 : $this->getHead()->getTitle()) . '</h1>';
	}
	
}