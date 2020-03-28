<?php
namespace Html\Page\JsonLd;

class Organization extends AbstractThing{
	
	protected function init() {
		//$this->data['sameAs'] = [];
	}
	
	public function addSocialUrl($url) {
		$this->data['sameAs'][] = $url;
		return $this;
	}
	
}