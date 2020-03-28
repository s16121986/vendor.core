<?php
namespace Validator\File;

use Validator\AbstractValidator;

class MimeType extends AbstractValidator{



	public function isValid($value) {
		if ($value instanceof \File) {
			if (in_array($value->mime_type, $this->mimeType)) {
				return true;
			}
		}
		return false;
	}

}