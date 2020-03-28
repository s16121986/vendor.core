<?php
namespace Auth\Provider;

class Profile{
	
	private static $assoc = array(
		'identifier' => array('id', 'uid'),
		'name' => array('firstname', 'first_name', 'givenName', 'first-name'),
		'surname' => array('last_name', 'lastname', 'familyName', 'last-name'),
		'presentation' => array('nick', 'displayName'),
		'language' => array('locale'),
		'description' => array('aboutMe', 'headline'),
		'gender' => array('sex'),
		'email' => array('mail', 'default_email')
	);
	
	private $data = array();
	private $provider;
	
	public function __construct($provider = null) {
		$this->provider = $provider;
	}
	
	public function __set($name, $value) {
		if (!isset(self::$assoc[$name])) {
			foreach (self::$assoc as $k => $names) {
				if (in_array($name, $names)) {
					$name = $k;
					break;
				}
			}
		}
		$this->data[$name] = $value;
	}
	
	public function __get($name) {
		if (isset($this->$name)) {
			return $this->$name;
		}
		return (isset($this->data[$name]) ? $this->data[$name] : null);
	}
	
	public function setData($data) {
		if ($data instanceof Response) {
			$data = $data->getData();
		}
		foreach ($data as $k => $v) {
			$this->$k = $v;
		}
		return $this;
	}
	
	public function getData() {
		return $this->data;
	}
	
}