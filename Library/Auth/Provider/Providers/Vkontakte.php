<?php
namespace Auth\Provider\Providers;

use Exception;

class Vkontakte extends OAuth2{

	// default permissions
	public $scope = "email";
	// default user fields map
	public $fields = array(
		// Old that saved for backward-compability
		'identifier' => 'uid',
		'firstName' => 'first_name',
		'lastName' => 'last_name',
		'displayName' => 'screen_name',
		'gender' => 'sex',
		'photoURL' => 'photo_big',
		'home_town' => 'home_town',
		'profileURL' => 'domain', // Will be converted in getUserByResponse()
		// New
		'nickname' => 'nickname',
		'bdate' => 'bdate',
		'timezone' => 'timezone',
		'photo_rec' => 'photo_rec',
		'domain' => 'domain',
		'photo_max' => 'photo_max',
		'home_phone' => 'home_phone',
		'city' => 'city', // Will be converted in getUserByResponse()
		'country' => 'country', // Will be converted in getUserByResponse()
	);

	protected function init() {
		parent::init();

		// Provider api end-points
		$this->api_base_url = 'https://api.vk.com/method/';
		$this->authorize_url = "https://oauth.vk.com/authorize";
		$this->token_url = "https://api.vk.com/oauth/token";
		$this->authorize_params = array(
			'display' => 'page',
			'v' => '5.52'
		);
		if (!empty($this->config['fields']))
			$this->fields = $this->config['fields'];
	}

	protected function loginFinish() {
		$response = parent::loginFinish();
		// store user id. it is required for api access to Vkontakte
		$this->storage
			->set('user_id', $response->user_id)
			->set('user_email', $response->email ? $response->email : null)
			->set('user_phone', $response->phone ? $response->phone : null);
	}

	/**
	 * load the user profile from the IDp api client
	 */
	public function getProfile() {
		// refresh tokens if needed
		$this->refreshToken();

		// Vkontakte requires user id, not just token for api access
		$params['uid'] = $this->storage->get('user_id');
		$params['fields'] = implode(',', $this->fields);
		// ask vkontakte api for user infos

		$response = $this->api('getProfiles', 'GET', $params);
		$response = $response->response;
		if (!isset($response[0]) || !isset($response[0]->uid) || isset($response->error)) {
			throw new Exception('User profile request failed! ' . $this->providerId . ' returned an invalid response.', 6);
		}
		$response = $response[0];

		// Fill datas
		$this->initProfile($this->profile, $response, true);
		// Additional data
		$this->profile->email = $this->storage->get('user_email');

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
