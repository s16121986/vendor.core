<?php
namespace Auth\Storage;

use Auth\User;

abstract class AbstractStorage{

	protected $_identity;
	protected $user;
	
	public function __construct(User $user) {
		$this->user = $user;
	}

	public function setIdentity($identity, $redirect = null) {
		$this->_identity = $identity;
		return $this;
	}

	public function getIdentity() {
		return $this->_identity;
	}

	public function hasIdentity() {
		return (null !== $this->_identity);
	}

	public function clear($params = null, $redirect = null) {
		$this->_identity = null;
		return $this;
	}

}