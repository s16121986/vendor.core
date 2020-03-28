<?php
namespace Grid\Column;

class Url extends AbstractColumn{

	protected $_options = array(
		'href' => '%value%',
		'target' => ''
	);
	
}