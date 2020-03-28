<?php
namespace Grid\Feature;

use Grid;

abstract class AbstractFeature{
	
	protected $grid = null;
	
	public function __construct(Grid $grid) {
		$this->grid = $grid;
	}
	
}