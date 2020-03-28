<?php
namespace Form\Element;

use Captcha as CaptchaImage;

class Captcha extends Text{

	protected $_options = array(
		'inputType' => 'text'
	);

	protected $_attributes = array();
	
	private $image;

	private function getCaptchaImage() {
		if (!$this->image) {
			$this->image = new CaptchaImage();
		}
		return $this->image;
	}
	
	public function checkValue($value) {
		return ($value === $this->getCaptchaImage()->getKeystring());
	}

	public function getHtml() {
		return '<div class="outer">'
				. '<img src="/captcha/" />'
				. '<input type="' . $this->inputType . '"' . $this->attrToString() . ' value="' . self::escape($this->getValue()) . '" />'
			. '</div>';
	}

}
