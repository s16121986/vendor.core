<?php
namespace Api\Router\Response;

abstract class AbstractFormat{
	
	protected $response;
	
	public function __construct($response) {
		$this->response = $response;
	}
	
	protected static function getFormattedDate() {
		return now()->format('Ymd\THis');
	}
	
	protected static function getStatusCode($result) {
		return ($result->isValid() ? 'ok' : 'error');
	}
	
	protected static function getStatusMessage($result) {
		return $result->getMessage();
	}
	
}