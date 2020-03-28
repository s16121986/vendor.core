<?php
namespace Auth\Provider\Providers;

use Exception;

class Telegram extends OAuth2{

	const URL_API = 'https://api.telegram.org/';
	const URL_BOT = 'https://telegram.me/';

	//149.154.167.40:443
	//149.154.167.50:443
	
	private static $state = 'DyeFWf41A53sdfKef424';

	private static $token;
	private static $baseUrl;

	protected function init() {
		//test app
		$this->id = '69792272';
		$this->secret = 'jnLBMSokyATu2upy_sbZ';
		
		parent::init();
		if (!$this->api_base_url) {
			$this->api_base_url = 'https://telegramlogin.com/';
		}
		$this->authorize_url = $this->api_base_url . 'token/' . $this->id;
		$this->token_url = $this->api_base_url . 'code';
		$this->authorize_params = array(
			'state' => self::$state
		);
	}

	public function authorizeUrl($extras = array()) {
		return $this->authorize_url . "?" . http_build_query($this->authorize_params);
	}
	
	public function getProfile() {
		$response = $this->api('user');
		if (!$response->id) {
			throw new Exception('User profile request failed! ' . $this->name . ' returned an invalid response.', 6);
		}
		$data = $response->telegram_user;
		$data->identifier = $data->telegram_id;
		return $this->profile->setData($data);
	}

}
