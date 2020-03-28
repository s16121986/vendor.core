<?php
namespace Stdlib;

use Dater;

class DateRange{
	
	const AUTO_INVERT = 1;
	
	private $start;
	private $end;
	
	private static function initDateTimeObject($value) {
		if (is_string($value)) {
			return Dater::initDatetimeObject($value);
		} elseif ($value instanceof \DateTime) {
			return $value;
		}
		return false;
	}
	
	public function __construct($start, $end = null, $options = null) {
		if (is_array($start)) {
			$options = $end;
			$end = null;
			foreach (array(1, 'end', 'date_end', 'time_end', 'to') as $n) {
				if (isset($start[$n])) {
					$end = $start[$n];
					break;
				}
			}
			foreach (array(0, 'start', 'date_start', 'time_start', 'from') as $n) {
				if (isset($start[$n])) {
					$start = $start[$n];
					break;
				}
			}
		}
		$this->start = self::initDateTimeObject($start);
		$this->end = self::initDateTimeObject($end);
	}
	
	public function __get($name) {
		return (isset($this->$name) ? $this->$name : null);
	}
	
	public function getStart($format = null) {
		if ($format) {
			return ($this->start ? $this->start->format($format) : '');
		}
		return $this->start;
	}
	
	public function getEnd($format = null) {
		if ($format) {
			return ($this->end ? $this->end->format($format) : '');
		}
		return $this->end;
	}
	
	public function isValid() {
		return ($this->start && $this->end && $this->start->format('Y-m-d h:i:s') <= $this->end->format('Y-m-d h:i:s'));
	}
	
}