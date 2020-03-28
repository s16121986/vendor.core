<?php

function Max() {
	$values = func_get_args();
	$max = null;
	foreach ($values as $value) {
		if (null === $max || $max < $value) {
			$max = $value;
		}
	}
	return $max;
}

function Min() {
	$values = func_get_args();
	$min = null;
	foreach ($values as $value) {
		if (null === $min || $min > $value) {
			$min = $value;
		}
	}
	return $min;
}