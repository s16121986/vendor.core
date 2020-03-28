<?php
namespace Auth\Provider\Providers;

use Exception;

class Linkedin extends OAuth2{
	
	private static $state = 'DyeFWf41A53sdfKef424';

	protected function init() {
		parent::init();
		$this->scope = 'r_basicprofile r_emailaddress';
		$this->api_base_url = "https://api.linkedin.com/";
		$this->authorize_url = 'https://www.linkedin.com/oauth/v2/authorization';
		$this->token_url = 'https://www.linkedin.com/oauth/v2/accessToken';
		$this->authorize_params = array(
			'state' => self::$state
		);
		$this->api_auth_type = 'bearer';
	}

	public function getProfile() {
		$response = $this->api('v1/people/~');
		$response->setFormat('xml');
		if (!$response->id) {
			throw new Exception('User profile request failed! ' . $this->name . ' returned an invalid response.', 6);
		}
		$this->profile->setData($response);
		return $this->profile;
	}

}
