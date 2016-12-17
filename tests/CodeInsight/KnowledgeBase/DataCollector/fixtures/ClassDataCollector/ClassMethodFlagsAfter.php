<?php

abstract class ClassMethods
{

	private function methodOne() {}

	public function methodTwo() {}

	protected function methodThree() {}

	final function methodFour();

	abstract function methodFive() {}

	function methodSix(...$numbers) {}

	static function methodSeven() {}

	function methodEight(): string {}

	function &methodNine() {}

}
