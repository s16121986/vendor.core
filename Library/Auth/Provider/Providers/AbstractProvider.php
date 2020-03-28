<?php

namespace Auth\Provider\Providers;

use Http\Session;
use Auth\Provider;
use Auth\Provider\Storage;
use Auth\Provider\Profile;
use Exception;

abstract class AbstractProvider {

	protected $config;
	protected $params;
	protected $provider;
	protected $profile;
	protected $storage;
	protected $endpoint;
	protected $authorizedFlag = false;

	public function __construct($config, $params = null) {
		$this->config = $config;
		$this->provider = str_replace('Auth\Provider\Providers\\', '', get_class($this));
		$this->storage = new Storage($this);
		$this->profile = new Profile($this->provider);

		if (!$params) {
			$params = $this->storage->get('params');
		}
		if (!isset($params['return_to'])) {
			$params['return_to'] = Provider::getCurrentUrl();
		}
		
		$this->params = $params;
		// set HybridAuth endpoint for this provider
		$this->endpoint = $this->storage->get('endpoint');

		$this->init();
	}

	protected function init() {
		
	}

	public function __get($name) {
		switch ($name) {
			case 'profile':
				return $this->getProfile();
			case 'name':
				return $this->provider;
			case 'endpoint':
			case 'provider':
				return $this->$name;
		}
		if (isset($this->config[$name])) {
			return $this->config[$name];
		}
		return (isset($this->params[$name]) ? $this->params[$name] : null);
	}

	public function __set($name, $value) {
		$this->params[$name] = $value;
	}

	public function getProfile() {
		return $this->profile;
	}

	public function authenticate($params = null) {
		if ($this->isAuthorized()) {
			return $this->getProfile();
		}
		return $this->login($params);
	}
	
	public function endpoint() {
		try {
			$this->loginFinish();
		} catch (Exception $e) {
			$this->setAuthorized(false);
			$this->clearStorage();
			throw $e;
		}
		$this->returnToCallbackUrl();
	}

	public function setAuthorized($flag) {
		$this->authorizedFlag = $flag;
		$this->storage->set('logged_in', $flag);
		return $this;
	}

	public function isAuthorized() {
		return ($this->authorizedFlag || $this->storage->get('logged_in'));
	}

	public function login($params = null) {
		/* foreach (Hybrid_Auth::$config['providers'] as $idpid => $params) {
		  Storage::delete('hauth_session.{$idpid}.hauth_return_to');
		  Storage::delete('hauth_session.{$idpid}.hauth_endpoint');
		  Storage::delete('hauth_session.{$idpid}.id_provider_params');
		  } */

		// make a fresh start
		$this->logout();

		if (!is_array($params)) {
			$params = array();
		}
		//$params['token'] = Session::getId();
		$params['time'] = time();
		$this->endpoint = Provider::getEndpointUrl();
		$this->storage
			->set('return_to', $this->return_to)
			->set('endpoint', $this->endpoint)
			->set('params', $params);

		// store config to be used by the end point
		Storage::config('provider', $this->name);
		//Storage::config('config', $this->config);
		try {
			$this->loginBegin();
		} catch (Exception $e) {
			$this->returnToCallbackUrl();
		}
		//self::redirect($params['login_start']);
	}

	public function logout() {
		$this->setAuthorized(false);
		$this->storage->clear();
		return $this;
	}

	protected function returnToCallbackUrl() {
		// get the stored callback url
		$callback_url = $this->storage->get('return_to');
		// remove some unneeded stored data
		$this->clearStorage();
		// back to home
		self::redirect($callback_url);
	}
	
	protected function clearStorage($full = false) {
		$this->storage
			->delete('return_to')
			->delete('endpoint')
			->delete('params');
		
		//Storage::config('provider', null);
		return $this;
	}

	abstract protected function loginBegin();

	abstract protected function loginFinish();

	public static function redirect($url, $mode = 'PHP') {
		// Ensure session is saved before sending response, see https://github.com/symfony/symfony/pull/12341
		if ((PHP_VERSION_ID >= 50400 && PHP_SESSION_ACTIVE === session_status()) || (PHP_VERSION_ID < 50400 && isset($_SESSION) && session_id())) {
			session_write_close();
		}

		if ($mode == 'PHP') {
			header('Location: ' . $url);
		} elseif ($mode == 'JS') {
			echo '<html>';
			echo '<head>';
			echo '<script type="text/javascript">';
			echo 'function redirect(){ window.top.location.href="' . $url . '"; }';
			echo '</script>';
			echo '</head>';
			echo '<body onload="redirect()">';
			echo 'Redirecting, please wait...';
			echo '</body>';
			echo '</html>';
		}
		die();
	}
}
