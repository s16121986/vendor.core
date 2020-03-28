<?php
namespace Auth\Provider;

use Exception;

class Google {

	public $api_base_url = "";
	public $authorize_url = "";
	public $token_url = "";
	public $token_info_url = "";
	public $authorize_params = array();
	public $client_id = "";
	public $client_key = "";
	public $client_secret = "";
	public $redirect_uri = "";
	public $access_token = "";
	public $refresh_token = "";
	public $access_token_expires_in = "";
	public $access_token_expires_at = "";
	//--

	public $sign_token_name = "access_token";
	public $decode_json = true;
	public $curl_time_out = 30;
	public $curl_connect_time_out = 30;
	public $curl_ssl_verifypeer = false;
	public $curl_ssl_verifyhost = false;
	public $curl_header = array();
	public $curl_useragent = "OAuth/2 Simple PHP Client v0.1.1; HybridAuth http://hybridauth.sourceforge.net/";
	public $curl_authenticate_method = "POST";
	public $curl_proxy = null;
	public $curl_compressed = false;
	//--

	public $http_code = "";
	public $http_info = "";
	protected $response = null;

	public function api($url, $method = "GET", $parameters = array()) {
		if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
			$url = $this->api_base_url . $url;
		}

		//$parameters[$this->sign_token_name] = $this->access_token;
		$parameters['key'] = $this->client_key;
		$response = null;

		switch ($method) {
			case 'GET' : $response = $this->request($url, $parameters, "GET");
				break;
			case 'POST' : $response = $this->request($url, $parameters, "POST");
				break;
		}

		if ($response && $this->decode_json) {
			return $this->response = json_decode($response);
		}

		return $this->response = $response;
	}

	public function authenticated() {
		if ($this->access_token) {
			if ($this->token_info_url && $this->refresh_token) {
				// check if this access token has expired,
				$tokeninfo = $this->tokenInfo($this->access_token);

				// if yes, access_token has expired, then ask for a new one
				if ($tokeninfo && isset($tokeninfo->error)) {
					$response = $this->refreshToken($this->refresh_token);

					// if wrong response
					if (!isset($response->access_token) || !$response->access_token) {
						throw new Exception("The Authorization Service has return an invalid response while requesting a new access token. given up!");
					}

					// set new access_token
					$this->access_token = $response->access_token;
				}
			}

			return true;
		}

		return false;
	}

	public function tokenInfo($accesstoken) {
		$params['access_token'] = $this->access_token;
		$response = $this->request($this->token_info_url, $params);
		return $this->parseRequestResult($response);
	}

	public function refreshToken($parameters = array()) {
		$params = array(
			"client_id" => $this->client_id,
			"client_secret" => $this->client_secret,
			"grant_type" => "refresh_token"
		);

		foreach ($parameters as $k => $v) {
			$params[$k] = $v;
		}

		$response = $this->request($this->token_url, $params, "POST");
		return $this->parseRequestResult($response);
	}

	private function request($url, $params = false, $type = "GET") {

		if ($type == "GET") {
			$url = $url . ( strpos($url, '?') ? '&' : '?' ) . http_build_query($params, '', '&');
		}

		$this->http_info = array();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_time_out);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->curl_useragent);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->curl_connect_time_out);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->curl_ssl_verifypeer);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->curl_ssl_verifyhost);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . $this->access_token
		));
		if ($this->curl_compressed) {
			curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
		}

		if ($this->curl_proxy) {
			curl_setopt($ch, CURLOPT_PROXY, $this->curl_proxy);
		}

		if ($type == "POST") {
			curl_setopt($ch, CURLOPT_POST, 1);
			if ($params)
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}

		$response = curl_exec($ch);
		if ($response === false) {
			
		}

		$this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ch));

		curl_close($ch);

		return $response;
	}

	private function parseRequestResult($result) {
		if (json_decode($result))
			return json_decode($result);

		parse_str($result, $output);

		$result = new StdClass();

		foreach ($output as $k => $v)
			$result->$k = $v;

		return $result;
	}

}
