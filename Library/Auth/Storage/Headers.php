<?php
namespace Auth\Storage;

class Headers extends AbstractStorage{
	
	protected $authParams;
	
	protected static function getHeader($header) {
        if (empty($header)) {
            
        }

        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER[$temp]) && $_SERVER[$temp])
            return $_SERVER[$temp];
		elseif (isset($_SERVER['REDIRECT_' . $temp]) && $_SERVER['REDIRECT_' . $temp])
			return $_SERVER['REDIRECT_' . $temp];

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
			$header = strtolower($header);
			foreach ($headers as $k => $v) {
				if (strtolower($k) == $header) {
					return $v;
				}
			}
        }

        return false;
    }
	
	public function getAuthParams() {
		return $this->authParams;
	}
	
	public function getIdentity() {
		if ($this->hasIdentity()) {
			return parent::getIdentity();
		}
		$authHeader = self::getHeader('Authorization');
		if (empty($authHeader)) {
			return null;
		}
		$this->authParams = \Auth\Util::split_header($authHeader);
		unset($authHeader);
		if (empty($this->authParams) || !isset($this->authParams['auth'])) {
			return null;
		}
		$authCode = $this->authParams['auth'];
		return $authCode;
	}
	
}
