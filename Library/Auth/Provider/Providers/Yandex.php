<?php
namespace Auth\Provider\Providers;

use stdClass;

class Yandex extends OAuth2{
	
	protected function init() {
		parent::init();
		$this->api_base_url = 'https://login.yandex.ru/';
		$this->authorize_url = "https://oauth.yandex.ru/authorize";
		$this->token_url = "https://oauth.yandex.ru/token";

		$this->api_auth_type = 'bearer';
		// Google POST methods require an access_token in the header
		//$this->curl_header = array("Authorization: OAuth " . $this->access_token);
	}
	
	public function getProfile() {
		$response = $this->api('info', 'GET', array('fields' => 'token_for_business'));
		if (!$response->id) {
			throw new Exception('User profile request failed! ' . $this->name . ' returned an invalid response.', 6);
		}
		//var_dump($this->api('me/ids_for_business', 'GET', array())->getData());
		$this->profile->setData($response);
		return $this->profile;
	}
	
}