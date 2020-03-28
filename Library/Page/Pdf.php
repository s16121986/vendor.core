<?php
namespace Page;
//https://pdflayer.com/dashboard
class Pdf{
	
	const URL = 'http://api.pdflayer.com/api/convert';
	const accessKey = 'bcafe8162ea89d15fcc3b4a289dc1e26';
	
	public function generate($url) {
		$params = array(
			'access_key' => self::accessKey,
			'document_url' => $url
		);
		header('Location: ' . self::URL . '?' . http_build_query($params));
		exit;
	}
	
}