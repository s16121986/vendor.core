<?php
namespace Console;

class Terminal{
	
	const S = "\e"; //\e
	const NL = "\n";
	
	const COLOR_BLACK = '0;30';
	const COLOR_DARKGREY = '1;30';
	const COLOR_RED = '0;31';
	const COLOR_LIGHTRED = '1;31';
	const COLOR_GREEN = '0;32';
	const COLOR_LIGHTGREEN	 = '1;32';
	const COLOR_BROWN = '0;33';
	const COLOR_YELLOW = '1;33';
	const COLOR_BLUE = '0;34';
	const COLOR_LIGHTBLUE = '1;34';
	const COLOR_MAGENTA = '0;35';
	const COLOR_LIGHTMAGENTA = '1;35';
	const COLOR_CYAN = '0;36';
	const COLOR_LIGHTCYAN = '1;36';
	const COLOR_LIGHTGREY = '0;37';
	const COLOR_WHITE = '1;37';
	
	const BACKGROUND_BLACK = 40;
	const BACKGROUND_RED = 41;
	const BACKGROUND_GREEN = 42;
	const BACKGROUND_YELLOW = 43;
	const BACKGROUND_BLUE = 44;
	const BACKGROUND_MAGENTA = 45;
	const BACKGROUND_CYAN = 46;
	const BACKGROUND_LIGHTGREY = 47;
	
	public static function out($string, $color = null, $background = null) {
		if (!is_string($string))
			$string = serialize($string);
		
		$colorString = '';
		if ($color)
			$colorString .= self::S . '[' . self::getColor('COLOR', $color) . 'm';
		if ($background)
			$colorString .= self::S . '[' . self::getColor('BACKGROUND', $background) . 'm';
		if ($colorString)
			$colorString = $colorString . $string . self::S . '[0m';
		else
			$colorString = $string;
		echo $colorString . self::NL;
	}
	
	private static function getColor($const, $value) {
		switch ($value) {
			case 'error': $value = 'red'; break;
			case 'success': $value = 'green'; break;
		}
		if (preg_match('/^\w+$/i', $value)) {
			return constant('self::' . $const . '_' . strtoupper($value));
		}
		return $value;
	}
	
}