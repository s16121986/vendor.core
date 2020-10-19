<?php

namespace Api\Router\Auth;

use Auth\Util as AuthUtil;

class Token extends AbstractAuth {

	public function authorization() {
		$authHeader = AuthUtil::getHeader('Authorization');
		if (empty($authHeader))
			return false;

		$authParams = AuthUtil::split_header($authHeader);
		if (empty($authParams) || !isset($authParams['authCode']) || self::AccessKeyValue !== $authParams['authCode'])
			return false;
	}

}