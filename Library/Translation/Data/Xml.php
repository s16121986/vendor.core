<?php
namespace Translation\Data;

use Exception;

class Xml extends AbstractData{
	
	private $xml = null;
	
	private static $filepath = null;
	
	public static function setFilepath($filepath) {
		self::$filepath = $filepath;
	}
	
	public static function getFilepath() {
		if (null === self::$filepath) {
			self::$filepath = \LIB_PATH . '/locale';
		}
		return self::$filepath;
	}
	
	public function getItems($asArray = true) {
		if ($asArray) {
			$items = array();
			foreach ($this->getXPath('/ldml/items/item', 'type') as $item) {
				$items[(string)$item->attributes()->type] = (string)$item;
			}
			return $items;
		} else {
			return $this->getXPath('/ldml/items/item', 'type');
		}
	}
	
	public function getContent($value, $path = 'item') {
		list($value, $path) = self::formatValue($value, $path);
		switch ($path) {
			case 'item':
				return $this->xpath('/ldml/items/item[@type=\'' . $value . '\']');
			case 'currencyname':
			case 'currency':
				return $this->xpath('/ldml/currencies/currency[@type=\'' . $value . '\']/displayName');
			case 'currencysymbol':
				return $this->xpath('/ldml/currencies/currency[@type=\'' . $value . '\']/symbol');
			case 'message':
				return $this->xpath('/ldml/messages/message[@type=\'' . $value . '\']');
			case 'error':
				return $this->xpath('/ldml/errors/error[@type=\'' . $value . '\']');
			case 'months':
				$valueTemp = array("gregorian", "format", "wide");
				if (empty($value)) {
					$value = $valueTemp;
				} elseif (!is_array($value)) {
					$valueTemp[2] = $value;
					$value = $valueTemp;
				}
				return  $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/months/monthContext[@type=\'' . $value[1] . '\']/monthWidth[@type=\'' . $value[2] . '\']/month', true);
			case 'month':
				$valueTemp = array("gregorian", "format", "wide");
				if (!is_array($value)) {
					array_unshift($valueTemp, $value);
					$value = $valueTemp;
				}
				return $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value[1] . '\']/months/monthContext[@type=\'' . $value[2] . '\']/monthWidth[@type=\'' . $value[3] . '\']/month[@type=\'' . $value[0] . '\']');
			case 'days':
				if (empty($value)) {
					$value = "gregorian";
				}
				$temp = array();
				$temp['context'] = "format";
				$temp['default'] = "wide";
				$temp['format']['abbreviated'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'format\']/dayWidth[@type=\'abbreviated\']/day', 'type');
				$temp['format']['narrow'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'format\']/dayWidth[@type=\'narrow\']/day', 'type');
				$temp['format']['wide'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'format\']/dayWidth[@type=\'wide\']/day', 'type');
				$temp['stand-alone']['abbreviated'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'stand-alone\']/dayWidth[@type=\'abbreviated\']/day', 'type');
				$temp['stand-alone']['narrow'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'stand-alone\']/dayWidth[@type=\'narrow\']/day', 'type');
				$temp['stand-alone']['wide'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'stand-alone\']/dayWidth[@type=\'wide\']/day', 'type');
				return $temp;
			case 'day':
				if (empty($value)) {
					$value = array("gregorian", "format", "wide");
				}
				return $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/days/dayContext[@type=\'' . $value[1] . '\']/dayWidth[@type=\'' . $value[2] . '\']/day', 'type');
			/*case 'week':
				$minDays = self::_calendarDetail($this->xpath('supplementalData', '/supplementalData/weekData/minDays', 'territories'));
				$firstDay = self::_calendarDetail($this->xpath('supplementalData', '/supplementalData/weekData/firstDay', 'territories'));
				$weekStart = self::_calendarDetail($this->xpath('supplementalData', '/supplementalData/weekData/weekendStart', 'territories'));
				$weekEnd = self::_calendarDetail($this->xpath('supplementalData', '/supplementalData/weekData/weekendEnd', 'territories'));

				$temp = $this->xpath('supplementalData', "/supplementalData/weekData/minDays[@territories='" . $minDays . "']", 'count', 'minDays');
				$temp += $this->xpath('supplementalData', "/supplementalData/weekData/firstDay[@territories='" . $firstDay . "']", 'day', 'firstDay');
				$temp += $this->xpath('supplementalData', "/supplementalData/weekData/weekendStart[@territories='" . $weekStart . "']", 'day', 'weekendStart');
				$temp += $this->xpath('supplementalData', "/supplementalData/weekData/weekendEnd[@territories='" . $weekEnd . "']", 'day', 'weekendEnd');
				return $temp;*/
			case 'quarters':
				if (empty($value)) {
					$value = "gregorian";
				}
				$temp = array();
				$temp['format']['abbreviated'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'format\']/quarterWidth[@type=\'abbreviated\']/quarter', 'type');
				$temp['format']['narrow'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'format\']/quarterWidth[@type=\'narrow\']/quarter', 'type');
				$temp['format']['wide'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'format\']/quarterWidth[@type=\'wide\']/quarter', 'type');
				$temp['stand-alone']['abbreviated'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'stand-alone\']/quarterWidth[@type=\'abbreviated\']/quarter', 'type');
				$temp['stand-alone']['narrow'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'stand-alone\']/quarterWidth[@type=\'narrow\']/quarter', 'type');
				$temp['stand-alone']['wide'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'stand-alone\']/quarterWidth[@type=\'wide\']/quarter', 'type');
				return $temp;
			case 'quarter':
				if (empty($value)) {
					$value = array("gregorian", "format", "wide");
				}
				return $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/quarters/quarterContext[@type=\'' . $value[1] . '\']/quarterWidth[@type=\'' . $value[2] . '\']/quarter', 'type');
			case 'eras':
				if (empty($value)) {
					$value = "gregorian";
				}
				$temp = array();
				$temp['names'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/eras/eraNames/era', 'type');
				$temp['abbreviated'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/eras/eraAbbr/era', 'type');
				$temp['narrow'] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/eras/eraNarrow/era', 'type');
				return $temp;
			case 'era':
				if (empty($value)) {
					$value = array("gregorian", "Abbr");
				}
				return $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/eras/era' . $value[1] . '/era', 'type');
			case 'date':
				if (empty($value)) {
					$value = "gregorian";
				}
				$temp = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'full\']/dateFormat/pattern', '', 'full');
				$temp += $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'long\']/dateFormat/pattern', '', 'long');
				$temp += $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'medium\']/dateFormat/pattern', '', 'medium');
				$temp += $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'short\']/dateFormat/pattern', '', 'short');
				return $temp;

			case 'time':
				if (empty($value)) {
					$value = "gregorian";
				}
				$temp = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'full\']/timeFormat/pattern', '', 'full');
				$temp += $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'long\']/timeFormat/pattern', '', 'long');
				$temp += $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'medium\']/timeFormat/pattern', '', 'medium');
				$temp += $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'short\']/timeFormat/pattern', '', 'short');
				return $temp;

			case 'datetime':
				if (empty($value)) {
					$value = "gregorian";
				}

				$timefull = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'full\']/timeFormat/pattern', '', 'full');
				$timelong = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'long\']/timeFormat/pattern', '', 'long');
				$timemedi = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'medium\']/timeFormat/pattern', '', 'medi');
				$timeshor = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'short\']/timeFormat/pattern', '', 'shor');

				$datefull = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'full\']/dateFormat/pattern', '', 'full');
				$datelong = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'long\']/dateFormat/pattern', '', 'long');
				$datemedi = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'medium\']/dateFormat/pattern', '', 'medi');
				$dateshor = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'short\']/dateFormat/pattern', '', 'shor');

				$full = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/dateTimeFormatLength[@type=\'full\']/dateTimeFormat/pattern', '', 'full');
				$long = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/dateTimeFormatLength[@type=\'long\']/dateTimeFormat/pattern', '', 'long');
				$medi = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/dateTimeFormatLength[@type=\'medium\']/dateTimeFormat/pattern', '', 'medi');
				$shor = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/dateTimeFormatLength[@type=\'short\']/dateTimeFormat/pattern', '', 'shor');
				$temp = array();
				$temp['full'] = str_replace(array('{0}', '{1}'), array($timefull['full'], $datefull['full']), $full['full']);
				$temp['long'] = str_replace(array('{0}', '{1}'), array($timelong['long'], $datelong['long']), $long['long']);
				$temp['medium'] = str_replace(array('{0}', '{1}'), array($timemedi['medi'], $datemedi['medi']), $medi['medi']);
				$temp['short'] = str_replace(array('{0}', '{1}'), array($timeshor['shor'], $dateshor['shor']), $shor['shor']);
				return $temp;
			case 'dateitem':
				if (empty($value)) {
					$value = "gregorian";
				}
				$temp = array();
				$_temp = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/availableFormats/dateFormatItem', 'id');
				foreach ($_temp as $key => $found) {
					$temp += $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/availableFormats/dateFormatItem[@id=\'' . $key . '\']', '', $key);
				}
				return $temp;
			case 'dateinterval':
				if (empty($value)) {
					$value = "gregorian";
				}
				$temp = array();
				$_temp = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/intervalFormats/intervalFormatItem', 'id');
				foreach ($_temp as $key => $found) {
					$temp[$key] = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/intervalFormats/intervalFormatItem[@id=\'' . $key . '\']/greatestDifference', 'id');
				}
				return $temp;
            case 'field':
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", $temp);
                }
                return $this->xpath('/ldml/dates/fields/field[@type=\'' . $value[1] . '\']/displayName', '', $value[1]);
            case 'relative':
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", $temp);
                }
				return $this->xpath('/ldml/dates/fields/field[@type=\'day\']/relative[@type=\'' . $value[1] . '\']');
                // $temp = $this->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/fields/field/relative[@type=\'' . $value[1] . '\']', '', $value[1]);
		}
		return null;
	}

	public function getXPath($path) {
		return $this->getXml()->xpath($path);
	}
	
	public function getXml() {
		if (null === $this->xml) {
			$filename = self::getFilepath() . ($this->path ? DIRECTORY_SEPARATOR . $this->path : '') . DIRECTORY_SEPARATOR . $this->language->code . '.xml';
			if (!file_exists($filename)) {
				#require_once 'Zend/Locale/Exception.php';
				throw new Exception("Missing locale file '$filename' for locale.");
			}
			$this->xml = simplexml_load_file($filename);
		}
		return $this->xml;
	}
	
	private function xpath($path, $attribute = null, $value = null, $array = false) {
		$temp = array();
		if (true === $attribute) {
			$array = true;
			$attribute = null;
		}
		$xpath = $this->getXPath($path);
		if (!empty($xpath)) {
			foreach ($xpath as &$found) {
				if (empty($value)) {
					if (empty($attribute)) {
						// Case 1
						$temp[] = (string) $found;
					} else if (empty($temp[(string) $found[$attribute]])) {
						// Case 2
						$temp[(string) $found[$attribute]] = (string) $found;
					}
				} else if (empty($temp[$value])) {
					if (empty($attribute)) {
						// Case 3
						$temp[$value] = (string) $found;
					} else {
						// Case 4
						$temp[$value] = (string) $found[$attribute];
					}
				}
			}
		}
		return ($array ? $temp : current($temp));
	}
	
}