<?php

class UserClass {

	public $userClassProperty;

	public function userClassMethod() {}

}

interface UserInterface {}

trait UserTrait {}

class ClassRelations extends UserClass implements UserInterface
{
	use UserTrait;

	public $classRelationsProperty;

	public function classRelationsMethod() {}
}

class ClassRelations2 extends Exception implements Traversable
{

}
