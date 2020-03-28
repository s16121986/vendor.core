<?php
/**
 * Класс перечислений
 */

abstract class Enum{

	/**
	 * Получить список констант класса
	 * 
	 * @return array
	 */
	protected static function _getConstatnts() {
		$cls = get_called_class();
		$refl = new ReflectionClass($cls);
		$const = $refl->getConstants();
		unset($refl);
		return $const;
	}

	/**
	 * Проверить наличие константы по значению
	 * 
     * @param $value Значение константы
	 * @return bool
	 */
	public static function valueExists($value) {
		return in_array($value, self::_getConstatnts());
	}

	/**
	 * Проверить наличие константы по наименованию
	 * 
     * @param $key Имя константы
	 * @return bool
	 */
	public static function keyExists($key) {
		return array_key_exists($key, self::_getConstatnts());
	}

	/**
	 * Получить значение константы
	 * 
     * @param $key Имя константы
	 * @return mixed
	 */
	public static function getValue($key) {
		//$key = strtoupper($key);
		foreach (self::_getConstatnts() as $k => $v) {
			if ($k == $key) {
				return $v;
			}
		}
		return null;
	}

	/**
	 * Получить значения констант
	 * 
	 * @return array
	 */
	public static function getValues() {
		return array_values(self::_getConstatnts());
	}

	/**
	 * Получить имя константы по значению
	 * 
     * @param $value Значение константы
	 * @return string
	 */
	public static function getKey($value) {
		foreach (self::_getConstatnts() as $k => $v) {
			if ($v == $value) {
				return $k;
			}
		}
		return null;
	}

	/**
	 * Получить полное наименование константы, включая имя класса
	 * 
     * @param $value Значение константы
	 * @return string
	 */
	public static function getName($value = null) {
		return get_called_class() . (null === $value ? '' : '_' . self::getKey($value));
	}

	/**
	 * Получить имена констант
	 * 
	 * @return array
	 */
	public static function getKeys() {
		return array_keys(self::_getConstatnts());
	}

	/**
	 * Получить константу по умолчанию (первую по счету)
	 * 
	 * @return mixed
	 */
	public static function getDefault() {
		$const = self::_getConstatnts();
		return array_shift($const);
	}

	/**
	 * Получить константы в виде массива
	 * 
     * @param bool $valueIndex (ключами выступают: true - значения, false - имена констант)
	 * @return array
	 */
	public static function asArray($valueIndex = false) {
		$array = array();
		if ($valueIndex) {
			foreach (self::_getConstatnts() as $k => $v) {
				$array[$v] = $k;
			}
		} else {
			foreach (self::_getConstatnts() as $k => $v) {
				$array[$k] = $v;
			}
		}
		return $array;
	}

	/**
	 * Получить локализованные имена констант
	 * 
	 * @return array
	 */
	public static function getLabels() {
		$cls = get_called_class();
		$array = array();
		foreach (self::_getConstatnts() as $k => $v) {
			$array[$v] = lang($cls . '_' . $k);
		}
		return $array;
	}

	/**
	 * Получить локализованное имя константы
	 * 
     * @param $val Значение константы
	 * @return string
	 */
	public static function getLabel($val) {
		$cls = get_called_class();
		$const = self::_getConstatnts();
		return (in_array($val, $const) ? lang($cls . '_' . array_search($val, $const)) : '');
	}

	/**
	 * Загрузить класс перечислений
	 * 
     * @param $name Модель
	 */
	public static function load($name) {
		$path = str_replace('\\', '/', $name);
		include_once MODELS_PATH . '/' . $path . '/Enums.php';
		return true;
	}
	
	public static function getHtml($val) {
		$cls = get_called_class();
		$const = self::_getConstatnts();
		return '<span class="' . $cls . ' ' . self::getKey($val) . '">' . (in_array($val, $const) ? lang($cls . '_' . array_search($val, $const)) : '') . '</span>';
	}

}