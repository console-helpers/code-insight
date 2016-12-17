<?php

class MethodParameters
{

	function greedyMethod(
		array $param_one,
		stdClass $param_two,
		string $param_three,
		callable $param_four,
		&$param_five,
		$param_six = 'def',
		$param_seven = PHP_EOL,
		$param_eight = null
	) {

	}

	function variadicMethod($param_nine, ...$param_ten) {

	}

}
