<?php
namespace Api;

abstract class Util{
	
	public static function toArray($array) {
		switch (true) {
			case is_array($array):break;
			case is_string($array):
				$array = explode(',', $array);
				break;
			default:
				return array();
		}
		return $array;
	}
	
	public static function normalizeIdArray($array) {
		$arrayTemp = self::toArray($array);
		$array = array();
		foreach ($arrayTemp as $id) {
			$id = (int)$id;
			if ($id > 0 && !in_array($id, $array)) {
				$array[] = $id;
			}
		}
		return $array;
	}
	
}