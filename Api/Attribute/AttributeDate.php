<?php

namespace Api\Attribute;

use Stdlib\DateTime;

class AttributeDate extends AbstractAttribute {

	const Date = 'date';
	const Time = 'time';
	const DateTime = 'datetime';

	protected $qualifiers = [
		'dateFractions' => self::Date,
		'maxValue' => null,
		'minValue' => null
	];

	private static function initDateTimeObject($value) {
		if (is_string($value))
			return DateTime::factory($value);
		elseif ($value instanceof \DateTime)
			return $value;

		return false;
	}

	public function checkValue($value) {
		if (parent::checkValue($value) && !empty($value))
			return (bool) self::initDateTimeObject($value);

		return false;
	}

	public function prepareValue($value) {
		if (empty($value) || !($dateTime = self::initDateTimeObject($value)))
			return null;

		return $this->toString($dateTime);
	}

	public function toString($value = null) {
		if (null === $value)
			$value = $this->getValue();
		else
			$value = self::initDateTimeObject($value);

		if (!$value)
			return '';

		switch ($this->dateFractions) {
			case self::Date:return DateTime::serverDate($value);
			case self::Time:return DateTime::serverTime($value);
			case self::DateTime:return DateTime::serverDatetime($value);
		}
	}

}
