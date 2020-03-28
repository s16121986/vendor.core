<?php
namespace Auth\Provider\Providers;

use Exception;

class Facebook extends OAuth2{

	protected function init() {
		parent::init();
		$this->api_base_url = "https://graph.facebook.com/";
		$this->authorize_url = "https://www.facebook.com/dialog/oauth";
		$this->token_url = "https://graph.facebook.com/oauth/access_token";
	}

	public function getProfile() {
		$response = $this->api('me', 'GET', array('fields' => 'id,name,email'));
		if (!$response->id) {
			throw new Exception('User profile request failed! ' . $this->name . ' returned an invalid response.', 6);
		}
		//var_dump($this->api('me/ids_for_business', 'GET', array())->getData());
		$this->profile->setData($response);
		return $this->profile;
	}

}
