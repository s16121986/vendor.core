<?php
abstract class AttributeType extends Enum {

	const String = 'string';
	const Number = 'number';
	const Boolean = 'boolean';
	const Date = 'date';
	const Url = 'url';
	const Enum = 'enum';
	const Predefined = 'predefined';
	const Foreign = 'foreign';
	const Hours = 'hours';
	const Year = 'year';
	const Table = 'table';
	const Model = 'model';
	const Password = 'password';
	const File = 'file';
	const Image = 'image';
	const Hierarchy = 'hierarchy';
	const Language = 'language';
	const Binary = 'binary';

}
abstract class ComparisonType extends Enum {

	private static $_typesAssoc = [
		self::Equal => ['='],
		self::Greater => ['>'],
		self::GreaterOrEqual => ['>='],
		self::Less => ['<'],
		self::LessOrEqual => ['<='],
		self::NotEqual => ['<>', '!', '!=']
	];

	const Equal = 1;
	const Greater = 2;
	const GreaterOrEqual = 3;
	const InList = 4;
	const Interval = 5;
	const CustomInterval = 15;
	const CustomIntervalIncludingBounds = 6;
	const IntervalIncludingBounds = 6;
	const IntervalIncludingLowerBound = 7;
	const IntervalIncludingUpperBound = 8;
	const Less = 9;
	const LessOrEqual = 10;
	const NotInList = 11;
	const NotEqual = 12;
	const Contains = 13;
	const NotContains = 14;
	
	public static function fromString($name) {
		foreach (self::_getConstatnts() as $key => $value) {
			if (strtolower($key) === $name) {
				return $value;
			}
		}
		foreach (self::$_typesAssoc as $value => $names) {
			if (in_array($name, $names)) {
				return $value;
			}
		}
		return null;
	}

}

abstract class SortDirection extends Enum{
	const ASC = 'asc';
	const DESC = 'desc';
}

abstract class DateFractions extends Enum{
	const DATE = 'date';
	const TIME = 'time';
	const DATETIME = 'datetime';
}

abstract class AllowedSign extends Enum{
	const ANY = 'any';
	const NONNEGATIVE = 'nonnegative';
}
abstract class AllowedLength extends Enum{
	const VARIABLE = 'variable';
	const FIXED = 'fixed';
}

abstract class ActionParams extends Enum{
	const ORDER = 'order';
	const LIMIT = 'limit';
	const COLUMNS = 'columns';
	const GROUP = 'group';
}

abstract class Chars {
	const CR = "\r";
	const VTab = "\v";
	//const NBSp = " ";
	const LF = "\n";
	const FF = "\f";
	const Tab = "\t";
}