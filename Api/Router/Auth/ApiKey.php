<?php

namespace Api\Router\Auth;

use Auth\Util as AuthUtil;

class ApiKey extends AbstractAuth {

	public function authorization() {
		$accessKey = AuthUtil::getHeader($this->keyName);

		return $accessKey === $this->keyValue;
	}

}