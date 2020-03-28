<?php

namespace Auth\Provider\Providers;

use Exception;

class Odnoklassniki extends OAuth2 {

	protected function init() {
		parent::init();
		$this->scope = 'VALUABLE_ACCESS';
		$this->api_base_url = "https://api.odnoklassniki.ru/fb.do";
		$this->authorize_url = "https://connect.ok.ru/oauth/authorize";
		$this->token_url = "https://api.ok.ru/oauth/token.do";
	}

	public function getProfile() {
		$sig = md5('application_key=' . $this->key . 'method=users.getCurrentUser' . md5($this->access_token . $this->client_secret));
		$response = $this->api('?application_key=' . $this->key . '&method=users.getCurrentUser&sig=' . $sig);
		if (!$response->uid) {
			throw new Exception('User profile request failed! ' . $this->name . ' returned an invalid response.', 6);
		}
		$this->profile->setData($response);
		return $this->profile;
	}

}
