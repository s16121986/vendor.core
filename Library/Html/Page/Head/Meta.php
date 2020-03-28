<?php
namespace Html\Page\Head;

class Meta extends AbstractMeta{
	
	public function __construct(array $attributes = []) {
		$this->setAttributes($attributes);
	}
	
	public function getIdentifier() {
		if (isset($this->attributes['http-equiv']))
			return 'meta_http_equiv_' . $this->getAttribute('http-equiv');
		elseif (isset($this->attributes['name']))
			return 'meta_name_' . $this->getAttribute('name');
		elseif (isset($this->attributes['property']))
			return 'meta_property_' . $this->getAttribute('property');
		return null;
	}
	
	public function getHtml() {
		return $this->_getHtml('meta', false);
	}
	
}