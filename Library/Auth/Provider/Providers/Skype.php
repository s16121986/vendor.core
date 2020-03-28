<?php
namespace Auth\Provider\Providers;

use Exception;

class Skype extends OAuth2 {

	protected function init() {
		$this->tenant = 'common';
		$this->scope = 'openid email profile https://graph.microsoft.com/user.read';// https://graph.microsoft.com/mail.read offline_access
		parent::init();
		$this->api_base_url = 'https://graph.microsoft.com/v1.0/';
		$this->authorize_url = 'https://login.microsoftonline.com/' . $this->tenant . '/oauth2/v2.0/authorize';
		$this->token_url = 'https://login.microsoftonline.com/' . $this->tenant . '/oauth2/v2.0/token';
		$this->authorize_params = array(
			'response_mode' => 'query'
		);
		$this->api_auth_type = 'bearer';
	}

	public function getProfile() {
		$response = $this->api('me/');
		if (!$response->id){
			throw new Exception('User profile request failed! ' . $this->name . ' returned an invalid response.', 6);
		}
		if (!$response->mail) {
			$response->mail = $response->userPrincipalName;
		}
		$this->profile->setData($response);
		return $this->profile;
	}

}