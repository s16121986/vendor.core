<?php
namespace Auth;

abstract class Util{

	public static function urlencode_rfc3986($input) {
		if (is_array($input)) {
			return array_map(array('OAuthUtil', 'urlencode_rfc3986'), $input);
		} else if (is_scalar($input)) {
			return str_replace(
				'+',
				' ',
				str_replace('%7E', '~', rawurlencode($input))
			);
		} else {
			return '';
		}
	}


	// This decode function isn't taking into consideration the above
	// modifications to the encoding process. However, this method doesn't
	// seem to be used anywhere so leaving it as is.
	public static function urldecode_rfc3986($string) {
		return urldecode($string);
	}

	// Utility function for turning the Authorization: header into
	// parameters, has to do some unescaping
	// Can filter out any non-oauth parameters if needed (default behaviour)
	// May 28th, 2010 - method updated to tjerk.meesters for a speed improvement.
	//                  see http://code.google.com/p/oauth/issues/detail?id=163
	public static function split_header($header, $only_allow_oauth_parameters = false) {
		$params = array();
		if (preg_match_all('/('.($only_allow_oauth_parameters ? 'Api' : '').'[a-z_-]*)=(:?"([^"]*)"|([^,]*))/i', $header, $matches)) {
			foreach ($matches[1] as $i => $h) {
				$params[$h] = self::urldecode_rfc3986(empty($matches[3][$i]) ? $matches[4][$i] : $matches[3][$i]);
			}
			if (isset($params['realm'])) {
				unset($params['realm']);
			}
		}
		return $params;
	}
	
	public static function getHeader($header) {
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER[$temp]) && $_SERVER[$temp])
            return $_SERVER[$temp];
		if (isset($_SERVER['REDIRECT_' . $temp]) && $_SERVER['REDIRECT_' . $temp])
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
	
	public static function getUserAgent() {
		return (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
	}

	public static function getClientIp($checkProxy = true) {
		$ip = null;
		if ($checkProxy && isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != null) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else if ($checkProxy && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != null) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
}