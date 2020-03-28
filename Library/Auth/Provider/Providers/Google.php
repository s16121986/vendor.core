<?php
namespace Auth\Provider\Providers;

use stdClass;
use Exception;

class Google extends OAuth2{
	
	protected function init() {
		parent::init();
		$this->scope = 'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/plus.profile.emails.read';
		$this->authorize_url = "https://accounts.google.com/o/oauth2/auth";
		$this->token_url = "https://accounts.google.com/o/oauth2/token";
		$this->token_info_url = "https://www.googleapis.com/oauth2/v2/tokeninfo";

		
		$this->api_auth_type = 'bearer';
		// Google POST methods require an access_token in the header
		//$this->curl_header = array("Authorization: OAuth " . $this->access_token);
	}
	
	public function getProfile() {
		// refresh tokens if needed
		$this->refreshToken();

		// ask google api for user infos
		if (strpos($this->scope, '/auth/plus.profile.emails.read') !== false) {
			$verified = $this->api("https://www.googleapis.com/plus/v1/people/me");

			if (!$verified->id || $verified->error)
				$verified = new stdClass();
		} else {
			$verified = $this->api("https://www.googleapis.com/plus/v1/people/me/openIdConnect");

			if (!$verified->sub || $verified->error)
				$verified = new stdClass();
		}

		$response = $this->api("https://www.googleapis.com/plus/v1/people/me");
		if (!$response->id || $response->error) {
			throw new Exception('User profile request failed! ' . $this->name . ' returned an invalid response.', 6);
		}

		$this->profile->setData($response);
		$this->profile->name = $response->name->givenName;
		$this->profile->surname = $response->name->familyName;
		if ($response->emails) {
			if (count($response->emails) == 1) {
				$this->profile->email = $response->emails[0]->value;
			} else {
				foreach ($response->emails as $email) {
					if ($email->type == 'account') {
						$this->profile->email = $email->value;
						break;
					}
				}
			}
			if (property_exists($verified, 'emails')) {
				if (count($verified->emails) == 1) {
					$this->profile->emailVerified = $verified->emails[0]->value;
				} else {
					foreach ($verified->emails as $email) {
						if ($email->type == 'account') {
							$this->profile->emailVerified = $email->value;
							break;
						}
					}
				}
			}
		}
		if (property_exists($response, 'placesLived')) {
			$this->profile->city = "";
			$this->profile->address = "";
			foreach ($response->placesLived as $c) {
				if (property_exists($c, 'primary')) {
					if ($c->primary == true) {
						$this->profile->address = $c->value;
						$this->profile->city = $c->value;
						break;
					}
				} else {
					if (property_exists($c, 'value')) {
						$this->profile->address = $c->value;
						$this->profile->city = $c->value;
					}
				}
			}
		}

		// google API returns multiple urls, but a "website" only if it is verified
		// see http://support.google.com/plus/answer/1713826?hl=en
		if (property_exists($response, 'urls')) {
			foreach ($response->urls as $u) {
				if (property_exists($u, 'primary') && $u->primary == true)
					$this->profile->webSiteURL = $u->value;
			}
		} else {
			$this->profile->webSiteURL = '';
		}
		// google API returns age ranges or min. age only (with plus.login scope)
		if (property_exists($response, 'ageRange')) {
			if (property_exists($response->ageRange, 'min') && property_exists($response->ageRange, 'max')) {
				$this->profile->age = $response->ageRange->min . ' - ' . $response->ageRange->max;
			} else {
				$this->profile->age = '> ' . $response->ageRange->min;
			}
		} else {
			$this->profile->age = '';
		}
		// google API returns birthdays only if a user set 'show in my account'
		if (property_exists($response, 'birthday')) {
			list($birthday_year, $birthday_month, $birthday_day) = explode('-', $response->birthday);

			$this->profile->birthDay = (int) $birthday_day;
			$this->profile->birthMonth = (int) $birthday_month;
			$this->profile->birthYear = (int) $birthday_year;
		} else {
			$this->profile->birthDay = 0;
			$this->profile->birthMonth = 0;
			$this->profile->birthYear = 0;
		}

		return $this->profile;
	}
	
}