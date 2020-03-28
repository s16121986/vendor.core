<?php
class Status{
	
	private $request;
	private $data = array(
		'status' => '',
		'name' => '',
		'text' => ''
	);
	
	public function __construct($request) {
		$this->request = $request;
	}
	
	public function __get($name) {
		return (isset($this->data[$name]) ? $this->data[$name] : null);
	}
	
	public function getQuery() {
		
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function isValidReferer($match = false) {
		$referer = $this->request->getServer('HTTP_REFERER');
		if (!$referer) {
			return false;
		}
		$host = $this->request->getScheme() . '://' . $this->request->getHttpHost();
		if (false === $match) {
			return (0 === strpos($referer, $host));
		} else {
			if (!$match) {
				$match = $this->request->getServer('REQUEST_URI');
			}
			return ($referer === $host . $match);
		}
	}
	
	public function setMessages($messages) {
		$this->messages = $messages;
		return $this;
	}
	
	function getQueryStatus() {
		$status = $this->request->getQuery('status');
		//$this->data = 'success';
		if (($k = $this->request->getQuery('success'))) {
			
			$status->name = $k;
			$status->success = $k;
			switch ($k) {
				case 'password':$status->text = lang('message_password_changed');break;
				case 'confirmation':$status->text = lang('message_account_confirmation_complete');break;
			}
		} elseif (($k = $this->getRequest()->getQuery('error'))) {
			$status->status = 'error';
			$status->name = $k;
			$status->error = $k;
			switch ($k) {
				case 'providerjoin':
					switch ($request->getQuery('code')) {
						case 'exists':$status->text = lang('account_socialref_exists', 'error');break;
						default:$status->text = lang('account_socialref_unknown', 'error');
					}
					break;
			}
		}
		return $status;
	}
	
}