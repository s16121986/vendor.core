<?php

namespace Auth\Provider\Providers;

use Exception;
use Auth\Provider\Response;

class OAuth2 extends AbstractProvider{
	
	const AUTH_TYPE_CODE = 'code';
	const AUTH_TYPE_BEARER = 'bearer';

	protected $api_auth_type = 'code';
	protected $authorize_url = "";
	protected $authorize_params = array();
	protected $token_url = "";
	protected $token_info_url = "";
	
	protected $sign_token_name = "access_token";
	protected $access_token = "";
	protected $refresh_token = "";
	protected $access_token_expires_in = "";
	protected $access_token_expires_at = "";
	//--

	protected $curl_time_out = 30;
	protected $curl_connect_time_out = 30;
	protected $curl_ssl_verifypeer = false;
	protected $curl_ssl_verifyhost = false;
	protected $curl_header = array();
	protected $curl_useragent = "OAuth/2 Simple PHP Client v0.1.1; HybridAuth http://hybridauth.sourceforge.net/";
	protected $curl_authenticate_method = "POST";
	protected $curl_proxy = null;
	protected $curl_compressed = false;
	//--

	protected function init() {
		parent::init();
		$this->curl_compressed = (bool)$this->compressed;

		$this->access_token = $this->storage->get('access_token');
		$this->refresh_token = $this->storage->get('refresh_token');
		$this->access_token_expires_in = $this->storage->get('expires_in');
		$this->access_token_expires_at = $this->storage->get('expires_at');

		// Set curl proxy if exist
		/* if (isset(Hybrid_Auth::$config["proxy"])) {
		  $this->api->curl_proxy = Hybrid_Auth::$config["proxy"];
		  } */
	}
	
	public function __get($name) {
		switch ($name) {
			case 'client_id':return $this->id;
			case 'client_key':return $this->key;
			case 'client_secret':return $this->secret;
			case 'redirect_uri':return $this->endpoint;
		}
		return parent::__get($name);
	}

	public function authorizeUrl($extras = array()) {
		$params = array(
			"client_id" => $this->client_id,
			"redirect_uri" => $this->redirect_uri,
			"response_type" => "code"
		);

		if (count($extras))
			foreach ($extras as $k => $v)
				$params[$k] = $v;
		if ($this->authorize_params) {
			foreach ($this->authorize_params as $k => $v)
				$params[$k] = $v;
		}
		return $this->authorize_url . "?" . http_build_query($params, '', '&');
	}

	public function _authenticate($code) {
		$params = array(
			"client_id" => $this->client_id,
			'scope' => $this->scope,
			"client_secret" => $this->client_secret,
			"grant_type" => "authorization_code",
			"redirect_uri" => $this->redirect_uri,
			"code" => $code
		);
		$response = $this->request($this->token_url, $params, $this->curl_authenticate_method);
		
		if (!$response->access_token) {
			throw new Exception("The Authorization Service has return: " . json_encode($response->error));
		}

		return $response;
	}

	public function isAuthorized() {
		if (parent::isAuthorized()) {
			return true;
		} elseif ($this->access_token) {
			if ($this->token_info_url && $this->refresh_token) {
				// check if this access token has expired,
				$tokeninfo = $this->tokenInfo($this->access_token);
				// if yes, access_token has expired, then ask for a new one
				if ($tokeninfo->error) {
					$this->refreshToken($this->refresh_token);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Format and sign an oauth for provider api
	 */
	public function api($url, $method = "GET", $parameters = array()) {
		if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
			$url = $this->api_base_url . $url;
		}
		$headers = array();
		switch ($this->api_auth_type) {
			case 'code':
				$parameters[$this->sign_token_name] = $this->access_token;
				break;
			case 'bearer':
				$headers[] = 'Authorization: Bearer ' . $this->access_token;
				break;
		}
		
		$response = null;

		switch ($method) {
			case 'GET' : $response = $this->request($url, $parameters, "GET", $headers);
				break;
			case 'POST' : $response = $this->request($url, $parameters, "POST", $headers);
				break;
		}
		return $this->response = $response;
	}

	public function getResponse() {
		return $this->response;
	}

	public function get($url, $parameters = array()) {
		return $this->api($url, 'GET', $parameters);
	}

	public function post($url, $parameters = array()) {
		return $this->api($url, 'POST', $parameters);
	}

	// -- tokens

	public function tokenInfo($accessToken = null) {
		if (null === $accessToken) {
			$accessToken = $this->access_token;
		}
		$params['access_token'] = $accessToken;
		return $this->request($this->token_info_url, $params);
	}

	public function refreshToken() {
		if ($this->access_token) {

			// have to refresh?
			if ($this->refresh_token && $this->access_token_expires_at) {

				// expired?
				if ($this->access_token_expires_at <= time()) {
					$params = array(
						"client_id" => $this->client_id,
						"client_secret" => $this->client_secret,
						"grant_type" => "refresh_token",
						"refresh_token" => $this->refresh_token
					);
					$response = $this->request($this->token_url, $params, "POST");
					if (!$response->access_token) {
						// set the user as disconnected at this point and throw an exception
						$this->setAuthorized(false);
						throw new Exception("The Authorization Service has return an invalid response while requesting a new access token. " . (string) $response->error);
					}

					// set new access_token
					$this->storeToken($response);
				}
			}
		}
	}

	// -- utilities

	protected function loginBegin() {
		$params = array("access_type" => "offline");
		$optionals = array("scope", "access_type", "redirect_uri", "approval_prompt", "hd", "state");

		foreach ($optionals as $param) {
			if ($this->$param) {
				$params[$param] = $this->$param;
			}
		}

		if ($this->force === true) {
			$params['approval_prompt'] = 'force';
		}
		self::redirect($this->authorizeUrl($params));
	}

	protected function loginFinish() {
		$error = (array_key_exists('error', $_REQUEST)) ? $_REQUEST['error'] : "";
		// check for errors
		if ($error) {
			throw new Exception("Authentication failed! {$this->provider} returned an error: $error", 5);
		}

		// try to authenticate user
		$code = (array_key_exists('code', $_REQUEST)) ? $_REQUEST['code'] : "";

		try {
			$response = $this->_authenticate($code);
		} catch (Exception $e) {
			throw new Exception("User profile request failed! {$this->provider} returned an error: $e", 6);
		}

		// check if authenticated
		if (!$response->access_token) {
			throw new Exception("Authentication failed! {$this->provider} returned an invalid access token.", 5);
		}

		$this->storeToken($response);
		return $response;
		// set user connected locally
	}

	private function request($url, $params = false, $type = "GET", $headers = array()) {
		if ($type == "GET") {
			$url = $url . ( strpos($url, '?') ? '&' : '?' ) . http_build_query($params, '', '&');
		}
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_time_out);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->curl_useragent);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->curl_connect_time_out);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->curl_ssl_verifypeer);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->curl_ssl_verifyhost);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($this->curl_header, $headers));

		if ($this->curl_compressed) {
			curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
		}

		if ($this->curl_proxy) {
			curl_setopt($ch, CURLOPT_PROXY, $this->curl_proxy);
		}

		if ($type == "POST") {
			curl_setopt($ch, CURLOPT_POST, 1);
			if ($params)
				curl_setopt($ch, CURLOPT_POSTFIELDS, (is_array($params) ? http_build_query($params) : $params));
		}

		$result = curl_exec($ch);
		if ($result === false) {
			//Hybrid_Logger::error("OAuth2Client::request(). curl_exec error: ", curl_error($ch));
		}
		//var_dump($result);exit;
		$response = new Response($result);
		$response
			->setHttpCode(curl_getinfo($ch, CURLINFO_HTTP_CODE))
			->setHttpInfo(curl_getinfo($ch));
		curl_close($ch);
		return $response;
	}
	
	private function storeToken($response = null) {
		if ($response) {
			$this->access_token = $response->access_token;
			$this->refresh_token = $response->refresh_token;
			$this->access_token_expires_in = $response->expires_in;
			if ($response->expires_in) {
				$this->access_token_expires_at = time() + $response->expires_in;
			}
		}
		$this->storage
			->set('access_token', $this->access_token)
			->set('refresh_token', $this->refresh_token)
			->set('expires_in', $this->access_token_expires_in)
			->set('expires_at', $this->access_token_expires_at);
		$this->setAuthorized(true);
		return $this;
	}

}
