<?php
namespace Auth\Provider\Providers;

use Exception;

class Twitter extends OAuth2{

	protected function init() {
		parent::init();
		$this->api_base_url = 'https://api.twitter.com/1.1/';
		$this->authorize_url = 'https://api.twitter.com/oauth/authorize';
		//$this->authorize_url = "https://api.twitter.com/oauth2/token";
		$this->token_url = "https://api.twitter.com/oauth2/token";
		$this->authorize_params = array(
			'grant_type' => 'client_credentials'
		);
		$this->api_auth_type = 'bearer';
	}

	/*protected function loginBegin() {
		$token = base64_encode(rawurlencode($this->client_key) . ':' . rawurlencode($this->client_secret));
		$parameters = array('grant_type' => 'client_credentials');
		$headers = array('Authorization: Basic  ' . $token);
		$response = $this->request($this->token_url, $parameters, "POST", $headers);
		self::redirect($this->authorizeUrl($params));
	}*/

	/**
	 * load the user profile from the IDp api client
	 */
	public function getProfile() {
		// refresh tokens if needed
		$this->refreshToken();
		$includeEmail = isset($this->config['includeEmail']) ? (bool) $this->config['includeEmail'] : false;
		$response = $this->api->get('account/verify_credentials.json'. ($includeEmail ? '?include_email=true' : ''));

		if (!isset($response->response[0]) || !isset($response->response[0]->uid) || isset($response->error)) {
			throw new Exception('User profile request failed! ' . $this->providerId . ' returned an invalid response.', 6);
		}

		// Fill datas
		$response = reset($response->response);
		$this->initProfile($this->profile, $response, true);

		return $this->profile;
	}

	/**
	 * load the user contacts
	 */
	public function getUserContacts() {
		$params = array(
			'fields' => implode(',', $this->fields),
		);

		$response = $this->api->api('friends.get', 'GET', $params);

		if (empty($response) || empty($response->response)) {
			return array();
		}

		$contacts = array();
		foreach ($response->response as $item) {
			$contacts[] = $this->getUserByResponse($item);
		}

		return $contacts;
	}

	/**
	 * @param object $response
	 * @param bool   $withAdditionalRequests True to get some full fields like 'city' or 'country'
	 *                                       (requires additional responses to vk api!)
	 *
	 * @return \Hybrid_User_Contact
	 */
	private function initProfile($profile, $response, $withAdditionalRequests = false) {

		if (property_exists($response, 'profileURL') && !empty($profile->profileURL)) {
			$profile->profileURL = 'http://vk.com/' . $profile->profileURL;
		}

		if (property_exists($response, 'gender')) {
			switch ($response->gender) {
				case 1: $profile->gender = 'female';
					break;
				case 2: $profile->gender = 'male';
					break;
			}
		}

		if (property_exists($response, 'bdate')) {
			$birthday = explode('.', $response->bdate);
			switch (count($birthday)) {
				case 3:
					$profile->birthDay = (int) $birthday[0];
					$profile->birthMonth = (int) $birthday[1];
					$profile->birthYear = (int) $birthday[2];
					break;

				case 2:
					$profile->birthDay = (int) $birthday[0];
					$profile->birthMonth = (int) $birthday[1];
					break;
			}
		}

		if (property_exists($response, 'city') && $withAdditionalRequests) {
			$params = array(
				'city_ids' => $response->city,
			);
			$cities = $this->api('database.getCitiesById', 'GET', $params);
			$city = reset($cities);
			if (is_array($city))
				$city = reset($city);
			if (is_object($city) || is_string($city)) {
				$profile->city = property_exists($city, 'name') ? $city->name : null;
			}
		}

		if (property_exists($response, 'country') && $withAdditionalRequests) {
			$params = array(
				'country_ids' => $response->country,
			);
			$countries = $this->api('database.getCountriesById', 'GET', $params);
			$country = reset($countries);
			if (is_array($country))
				$country = reset($country);
			if (is_object($country) || is_string($country)) {
				$profile->country = property_exists($country, 'name') ? $country->name : null;
			}
		}

		foreach ($this->fields as $field => $map) {
			if (!$profile->$field)
				$profile->$field = (property_exists($response, $map)) ? $response->$map : null;
		}

		return $profile;
	}

}
