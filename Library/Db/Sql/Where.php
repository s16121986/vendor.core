<?php
namespace Db\Sql;

class Where{

	private static $_simpleComparisonTypes = array(
		\ComparisonType::Equal,
		\ComparisonType::Greater,
		\ComparisonType::GreaterOrEqual,
		\ComparisonType::Less,
		\ComparisonType::LessOrEqual,
		\ComparisonType::NotEqual,
	);

	private static $_comparisonTypesAssoc = array(
		\ComparisonType::Equal => '=',
		\ComparisonType::NotEqual => '<>',
		\ComparisonType::Greater => '>',
		\ComparisonType::GreaterOrEqual => '>=',
		\ComparisonType::Less => '<',
		\ComparisonType::LessOrEqual => '<=',
		\ComparisonType::InList => 'IN',
		\ComparisonType::NotInList => 'NOT IN',
		\ComparisonType::Interval => array('>', '<'),
		\ComparisonType::IntervalIncludingBounds => array('>=', '<='),
		\ComparisonType::IntervalIncludingLowerBound => array('>=', '<'),
		\ComparisonType::IntervalIncludingUpperBound => array('>', '<='),
		\ComparisonType::Contains => 'LIKE',
		\ComparisonType::NotContains => 'NOT LIKE'
	);
	
	public function addPredicate() {
		
	}
	
	public function equal($identifier, $value) {
		$this->addPredicate($identifier, $value);
	}
	
}