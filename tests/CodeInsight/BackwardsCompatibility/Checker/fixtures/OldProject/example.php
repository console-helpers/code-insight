<?php
define('SOME_CONST', 1);

define('SAME_CONST', 1);

function functionA() {}

function functionSameEmpty() {}
function functionSameNonEmpty($p1) {}

function functionSiFromEmptyToNonEmpty() {}
function functionSiFromNonEmptyToEmpty($p1) {}
function functionSiFromNonEmptyToNonEmpty($p1) {}

class kEvent {}

class ClassA {}

interface InterfaceA {}

trait TraitA {}

class ClassB {}

class ClassC {}

class ClassD {
	const SOME_CONST = 1;

	const SAME_CONST = 1;

	public $publicProperty;
	protected $protectedProperty;
	private $privateProperty;

	public $publicPropertySr;
	public $publicToPrivatePropertySr;
	protected $protectedPropertySr;

	public $publicPropertySame;
	protected $protectedPropertySame;
	private $privatePropertySame;

	public function ClassD() {}

	public function publicMethodSameEmpty() {}
	protected function protectedMethodSameEmpty() {}
	private function privateMethodSameEmpty() {}

	public function publicMethodSameNonEmpty($p1) {}
	protected function protectedMethodSameNonEmpty($p1) {}
	private function privateMethodSameNonEmpty($p1) {}

	public function publicMethod() {}
	protected function protectedMethod() {}
	private function privateMethod() {}

	public function publicMethodSr() {}
	public function publicToPrivateMethodSr() {}
	protected function protectedMethodSr() {}

	public function publicMethodAb() {}
	protected function protectedMethodAb() {}
	private function privateMethodAb() {}

	public function publicMethodFi() {}
	protected function protectedMethodFi() {}
	private function privateMethodFi() {}

	public function publicMethodSiFromEmptyToNonEmpty() {}
	protected function protectedMethodSiFromEmptyToNonEmpty() {}
	private function privateMethodSiFromEmptyToNonEmpty() {}

	public function publicMethodSiFromNonEmptyToEmpty($p1) {}
	protected function protectedMethodSiFromNonEmptyToEmpty($p1) {}
	private function privateMethodSiFromNonEmptyToEmpty($p1) {}

	public function publicMethodSiFromNonEmptyToNonEmpty($p1) {}
	protected function protectedMethodSiFromNonEmptyToNonEmpty($p1) {}
	private function privateMethodSiFromNonEmptyToNonEmpty($p1) {}
}

class ClassE extends ClassD {}

// In-Portal specifics.
class ExampleEventHandler
{
	function mapPermissions() {}

	function SetCustomQuery() {}

	function OnEventSr(kEvent $event) {}

	function OnEventSig1(&$event) {}

	function OnEventSig2(&$event) {}

	function OnEventSig3($event) {}
}

class AdminEventsHandler
{
	function mapPermissions() {}

	function SetCustomQuery() {}

	function OnEventSr(kEvent $event) {}

	function OnEventSig1(&$event) {}

	function OnEventSig2(&$event) {}

	function OnEventSig3($event) {}
}

class ExampleTagProcessor
{
	function TagNameOneSr(array $params) {}

	function TagNameTwoSr($params) {}

	function notTagNameOneSr(array $params) {}

	function notTagNameTwoSr($params) {}
}
