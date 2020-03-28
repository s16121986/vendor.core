<?php
namespace Grid\Column;

class Email extends AbstractColumn{

	protected $_options = array(
		'href' => 'mailto:%value%'
	);
	
}