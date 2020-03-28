<?php
namespace Auth;

use Db;
use Auth;
use Auth\Exception;
use Menu\Menu;
use Auth\Provider\Storage;
use Auth\Provider\Profile;

abstract class Provider{
	
	private static $config = null;
	private static $models = array();
	
	protected $data = null;
	protected $adapter;
	protected $exception = null;
	
	public static function factory($model) {
		$config = self::getConfig('providers', strtolower($model));
		if ($config) {
			$cls = 'Auth\Provider\Providers\\' . ucfirst($model);
			$provider = new $cls($config);
			return $provider;
		} else {
			throw new Exception('Provider invalid (' . $model . ')');
		}
		return null;
	}
	
	public static function authenticate($model = null, $params = null) {
		if (null === $params) {
			$params = $_REQUEST;
		}
		if (null === $model) {
			if (isset($params['provider'])) {
				$model = $params['provider'];
			} else {
				$model = Storage::config('provider');
			}
		}
		
		if (null === $model) {
			if (isset($params['get'])) {
				switch ($params['get']) {
					case 'openid_policy':
						return self::processOpenidPolicy();
					case 'openid_xrds':
						return self::processOpenidXRDS();
				}
				return self::processOpenidRealm();
			}
			return self::processOpenidRealm();
		} elseif (($provider = self::factory($model))) {
			return $provider->authenticate($params);
		}
		return null;
	}
	
	public static function endpoint() {
		$model = Storage::config('provider');
		if (!$model) {
			throw new Exception('storage model undefined');
		}
		if (($provider = self::factory($model))) {
			$provider->endpoint();
		}
		return false;
	}
	
	public static function login($data) {
		if (is_string($data)) {
			$data = array(
				'provider' => $data
			);
		} elseif ($data instanceof Profile) {
			$data = array(
				'provider' => $data->provider,
				'identifier' => $data->identifier
			);
		}
		if (!isset($data['provider'])) {
			return null;
		}
		if (isset($data['identifier'])) {
			$t = Auth::getUser()->oauth;
			$authRow = Db::from($t, array('id', 'user_id', 'provider', 'identifier'))
				->joinInner('users', 'users.id=' . $t . '.user_id', null)
				->where($t . '.provider=?', $data['provider'])
				->where($t . '.identifier=?', $data['identifier'])
				->query()->fetchRow();
			if (empty($authRow)) {
				//$this->exception = new Exception(Exception::USER_NOT_FOUND);
				return false;
			}
			//$this->data = $authRow;
			Auth::getUser()->setId($authRow['user_id']);
			return true;
		} else {
			return self::authenticate($data['provider'], $data);
		}
	}
	
	public static function isValid() {
		return (null === $this->exception);
	}
	
	public static function setConfig($config) {
		self::$config = $config;
	}
	
	public static function getConfig() {
		$config = self::$config;
		foreach (func_get_args() as $k) {
			if (!isset($config[$k])) {
				return null;
			}
			$config = $config[$k];
		}
		return $config;
	}
	
	public static function getMenu($options = array()) {
		$menu = new Menu(array_merge(array('class' => 'social'), $options));
		if (($providers = self::getConfig('providers'))) {
			foreach ($providers as $k => $provider) {
				if (!isset($provider['url']) || !$provider['url']) {
					continue;
				}
				$menu->add(array(
					'title' => (isset($provider['title']) ? $provider['title'] : null),
					'attr' => 'target="_blank"',
					'href' => $provider['url'],
					'class' => strtolower($k)
				));
			}
		}
		return $menu;
	}
	
	public static function getProviderConfig($provider, $default = array()) {
		if (isset(self::$config[$provider])) {
			return array_merge(self::$config[$provider], $default);
		}
		return $default;
	}

	public static function getCurrentUrl($request_uri = true) {
		if (php_sapi_name() == 'cli') {
			return '';
		}

		$protocol = 'http://';

		if ((isset($_SERVER['HTTPS']) && ( $_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1 ))
				|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))
		{
			$protocol = 'https://';
		}

		$url = $protocol . $_SERVER['HTTP_HOST'];

		if ($request_uri) {
			$url .= $_SERVER['REQUEST_URI'];
		} else {
			$url .= $_SERVER['PHP_SELF'];
		}

		// return current url
		return $url;
	}
	
	public static function getEndpointUrl() {
		$url = self::getConfig('endpoint');
		if (empty($url)) {
			// the base url wasn't provide, so we must use the current
			// url (which makes sense actually)
			$url = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https';
			$url .= '://' . $_SERVER['HTTP_HOST'];
			$url .= $_SERVER['REQUEST_URI'];
		}
		return $url;
	}
	
	protected static function processOpenidPolicy() {
		$output = file_get_contents(dirname(__FILE__) . "/resources/openid_policy.html");
		print $output;
		die();
	}

	protected static function processOpenidXRDS() {
		die('processOpenidRealm');
		header("Content-Type: application/xrds+xml");

		$output = str_replace("{RETURN_TO_URL}", str_replace(
				array("<", ">", "\"", "'", "&"), array("&lt;", "&gt;", "&quot;", "&apos;", "&amp;"), self::getCurrentUrl(false)
			), file_get_contents(dirname(__FILE__) . "/resources/openid_xrds.xml"));
		print $output;
		die();
	}

	protected static function processOpenidRealm() {
		die('processOpenidRealm');
		$output = str_replace("{X_XRDS_LOCATION}", htmlentities(self::getCurrentUrl(false), ENT_QUOTES, 'UTF-8')
			. "?get=openid_xrds&v="
			. 1, file_get_contents(dirname(__FILE__) . "/resources/openid_realm.html"));
		print $output;
		die();
	}
	
}