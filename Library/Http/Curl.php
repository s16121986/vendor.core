<?php
namespace Http;

use Http\Curl\Response;

class Curl{
	
	private $url;
	private $method = 'GET';
	private $parameters = [];
	private $parameters_format;
	private $headers = [];
	private $options = [];
	
	public function __construct($url = null) {
		$this->setUrl($url);
		$this->setOptions([
			//CURLOPT_USERAGENT => 'ConsoleSyncBot',
			//CURLOPT_SSL_VERIFYPEER => false,
			//CURLOPT_SSL_VERIFYHOST => false,
			
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT => 300
		]);
	}
	
	public function setUrl($url) {
		$this->url = $url;
	}
	
	public function setMethod($method) {
		$this->method = $method;
	}
	
	public function setParameters(array $parameters, $format = null) {
		$this->parameters = $parameters;
	}
	
	public function setHeaders(array $headers) {
		foreach ($headers as $k => $v)
			$this->setHeader ($k, $v);
	}
	
	public function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}
	
	public function setOptions(array $options) {
		foreach ($options as $k => $v)
			$this->setOption($k, $v);
	}
	
	public function setOption($name, $value) {
		$this->options[$name] = $value;
	}
	
	public function send() {
		$requestUrl = $this->url;
		$options = $this->options;
		switch ($this->method) {
			case 'GET':
				if ($this->parameters)
					$requestUrl .= '?' . http_build_query($this->parameters);
				break;
			case 'POST':
				$options[CURLOPT_POST] = true;
				switch ($this->parameters_format) {
					case 'json':
						$options[CURLOPT_POSTFIELDS] = json_encode($this->parameters);
						break;
					default:
						$options[CURLOPT_POSTFIELDS] = $this->parameters;
				}
				break;
			default:
				throw new Exception('Method invalid');
		}
		
		$headers = $this->headers;
		$options[CURLOPT_HTTPHEADER] = $headers;
		$ch = curl_init($requestUrl);
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		$response = new Response();
		if ($result === false) {
			$errno = curl_errno($ch);
			$error = curl_error($ch);
			curl_close($ch);
			$response->setException("Curl returned error $errno: $error\n");
		} else {
			$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$response->setHttpCode($httpCode);
			curl_close($ch);
			if ($httpCode >= 500) {
				// do not wat to DDOS server if something goes wrong
				sleep(10);
			} else if ($httpCode !== 200) {
				if ($httpCode === 401) {
					$response->setException('Invalid access token provided');
				} else {
					$response->setException('Request has failed with error:' . var_export($response, true));
				}
			} else {
				$response->setContent($result);
			}
		}
		return $response;
	}
	
}