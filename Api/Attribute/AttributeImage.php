<?php
namespace Api\Attribute;

class AttributeImage extends AttributeFile{

	public function __construct($name, $qualifiers = null) {
		$plugin = array();
		foreach (array('size', 'sizes') as $k) {
			if (isset($qualifiers[$k])) {
				$plugin[$k] = $qualifiers[$k];
				unset($qualifiers[$k]);
			}
		}
		if (!empty($plugin)) {
			$this->addPlugin('ImageResize', $plugin);
		}
		parent::__construct($name, $qualifiers);
	}

}