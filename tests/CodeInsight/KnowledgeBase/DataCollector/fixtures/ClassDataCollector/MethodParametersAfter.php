<?php

class MethodParameters
{

	function greedyMethod(
		stdClass $param_one,
		array $param_two,
		callable $param_three,
		string $param_four,
		$param_five = 'def',
		&$param_six = true,
		$param_seven = null,
		$param_eight = PHP_EOL
	) {

	}

	function variadicMethod($param_ten, ...$param_nine) {

	}

}
