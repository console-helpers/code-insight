<?php

abstract class ClassMethods
{

	public function methodOne() {}

	protected function methodTwo() {}

	private function methodThree() {}

	abstract function methodFour();

	final function methodFive() {}

	static function methodSix() {}

	function methodSeven(...$numbers) {}

	function &methodEight() {}

	function methodNine(): string {}

}
