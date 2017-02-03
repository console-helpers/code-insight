<?php
define('SOME_CONST', 1);

define('SAME_CONST', 1);

function functionA() {}

function functionSameEmpty() {}
function functionSameNonEmpty($p1) {}

function functionSiFromEmptyToNonEmpty() {}
function functionSiFromNonEmptyToEmpty($p1) {}
function functionSiFromNonEmptyToNonEmpty($p1) {}

function functionCompatibleSig1() {}
function functionCompatibleSig2($p1) {}
function functionCompatibleSig3($p1) {}
function functionIncompatibleSig1($p1) {}

// Class used only for type hinting.
class kEvent {}

// Class, that will be removed.
class ClassA {}

// Interface, that will be removed.
interface InterfaceA {}

// Trait, that will be removed.
trait TraitA {}

// Class, that will be made abstract.
class ClassB {}

// Class, that will be made final.
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

	public $publicPropertyMs;
	protected $protectedPropertyMs;
	private $privatePropertyMs;

	public static $publicPropertyMns;
	protected static $protectedPropertyMns;
	private static $privatePropertyMns;

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

	public function publicMethodMs() {}
	protected function protectedMethodMs() {}
	private function privateMethodMs() {}

	public static function publicMethodMns() {}
	protected static function protectedMethodMns() {}
	private static function privateMethodMns() {}

	public function publicMethodSiFromEmptyToNonEmpty() {}
	protected function protectedMethodSiFromEmptyToNonEmpty() {}
	private function privateMethodSiFromEmptyToNonEmpty() {}

	public function publicMethodSiFromNonEmptyToEmpty($p1) {}
	protected function protectedMethodSiFromNonEmptyToEmpty($p1) {}
	private function privateMethodSiFromNonEmptyToEmpty($p1) {}

	public function publicMethodSiFromNonEmptyToNonEmpty($p1) {}
	protected function protectedMethodSiFromNonEmptyToNonEmpty($p1) {}
	private function privateMethodSiFromNonEmptyToNonEmpty($p1) {}

	public function publicMethodCompatibleSig1() {}
	protected function protectedMethodCompatibleSig1() {}
	private function privateMethodCompatibleSig1() {}

	public function publicMethodCompatibleSig2($p1) {}
	protected function protectedMethodCompatibleSig2($p1) {}
	private function privateMethodCompatibleSig2($p1) {}

	public function publicMethodCompatibleSig3($p1) {}
	protected function protectedMethodCompatibleSig3($p1) {}
	private function privateMethodCompatibleSig3($p1) {}

	public function publicMethodIncompatibleSig1($p1) {}
	protected function protectedMethodIncompatibleSig1($p1) {}
	private function privateMethodIncompatibleSig1($p1) {}
}

// Only report BC breaks for class, where class member is declared.
class ClassE extends ClassD {}

// Renaming PHP5 into PHP4 constructor isn't a BC break.
class ClassF {
	public function __construct() {}
}

// Class that stayed final isn't checked for changes in protected class members.
final class ClassG {
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

	public $publicPropertyMs;
	protected $protectedPropertyMs;
	private $privatePropertyMs;

	public static $publicPropertyMns;
	protected static $protectedPropertyMns;
	private static $privatePropertyMns;

	public function ClassG() {}

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

	public function publicMethodMs() {}
	protected function protectedMethodMs() {}
	private function privateMethodMs() {}

	public static function publicMethodMns() {}
	protected static function protectedMethodMns() {}
	private static function privateMethodMns() {}

	public function publicMethodSiFromEmptyToNonEmpty() {}
	protected function protectedMethodSiFromEmptyToNonEmpty() {}
	private function privateMethodSiFromEmptyToNonEmpty() {}

	public function publicMethodSiFromNonEmptyToEmpty($p1) {}
	protected function protectedMethodSiFromNonEmptyToEmpty($p1) {}
	private function privateMethodSiFromNonEmptyToEmpty($p1) {}

	public function publicMethodSiFromNonEmptyToNonEmpty($p1) {}
	protected function protectedMethodSiFromNonEmptyToNonEmpty($p1) {}
	private function privateMethodSiFromNonEmptyToNonEmpty($p1) {}

	public function publicMethodCompatibleSig1() {}
	protected function protectedMethodCompatibleSig1() {}
	private function privateMethodCompatibleSig1() {}

	public function publicMethodCompatibleSig2($p1) {}
	protected function protectedMethodCompatibleSig2($p1) {}
	private function privateMethodCompatibleSig2($p1) {}

	public function publicMethodCompatibleSig3($p1) {}
	protected function protectedMethodCompatibleSig3($p1) {}
	private function privateMethodCompatibleSig3($p1) {}

	public function publicMethodIncompatibleSig1($p1) {}
	protected function protectedMethodIncompatibleSig1($p1) {}
	private function privateMethodIncompatibleSig1($p1) {}
}

// Class, that was made final is checking for all usual stuff.
class ClassH {
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

	public $publicPropertyMs;
	protected $protectedPropertyMs;
	private $privatePropertyMs;

	public static $publicPropertyMns;
	protected static $protectedPropertyMns;
	private static $privatePropertyMns;

	public function ClassH() {}

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

	public function publicMethodMs() {}
	protected function protectedMethodMs() {}
	private function privateMethodMs() {}

	public static function publicMethodMns() {}
	protected static function protectedMethodMns() {}
	private static function privateMethodMns() {}

	public function publicMethodSiFromEmptyToNonEmpty() {}
	protected function protectedMethodSiFromEmptyToNonEmpty() {}
	private function privateMethodSiFromEmptyToNonEmpty() {}

	public function publicMethodSiFromNonEmptyToEmpty($p1) {}
	protected function protectedMethodSiFromNonEmptyToEmpty($p1) {}
	private function privateMethodSiFromNonEmptyToEmpty($p1) {}

	public function publicMethodSiFromNonEmptyToNonEmpty($p1) {}
	protected function protectedMethodSiFromNonEmptyToNonEmpty($p1) {}
	private function privateMethodSiFromNonEmptyToNonEmpty($p1) {}

	public function publicMethodCompatibleSig1() {}
	protected function protectedMethodCompatibleSig1() {}
	private function privateMethodCompatibleSig1() {}

	public function publicMethodCompatibleSig2($p1) {}
	protected function protectedMethodCompatibleSig2($p1) {}
	private function privateMethodCompatibleSig2($p1) {}

	public function publicMethodCompatibleSig3($p1) {}
	protected function protectedMethodCompatibleSig3($p1) {}
	private function privateMethodCompatibleSig3($p1) {}

	public function publicMethodIncompatibleSig1($p1) {}
	protected function protectedMethodIncompatibleSig1($p1) {}
	private function privateMethodIncompatibleSig1($p1) {}
}

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
