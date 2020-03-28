<?php

namespace Auth\Provider\Providers;

use Exception;

class Mailru extends OAuth2 {

	protected function init() {
		parent::init();
		$this->api_base_url = "http://www.appsmail.ru/platform/api";
		$this->authorize_url = "https://connect.mail.ru/oauth/authorize";
		$this->token_url = "https://connect.mail.ru/oauth/token";
		$this->sign_token_name = "session_key";
	}

	public function getProfile() {
		$sig = md5("client_id=" . $this->client_id . "format=jsonmethod=users.getInfosecure=1session_key=" . $this->access_token . $this->client_secret);
		$response = $this->api("?format=json&client_id=" . $this->client_id . "&method=users.getInfo&secure=1&sig=" . $sig);
		
		$data = $response->getData();
		if (!isset($data[0]->uid)) {
			throw new Exception('User profile request failed! ' . $this->name . ' returned an invalid response.', 6);
		}
		$this->profile->setData($data[0]);

		return $this->profile;
	}

}
