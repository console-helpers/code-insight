<?php
define('SAME_CONST', 1);

function functionSameEmpty() {}
function functionSameNonEmpty($p1) {}

function functionSiFromEmptyToNonEmpty($p1) {}
function functionSiFromNonEmptyToEmpty() {}
function functionSiFromNonEmptyToNonEmpty($p1, $p2) {}

function functionCompatibleSig1($p1 = null) {}
function functionCompatibleSig2($p1, $p2 = null) {}
function functionCompatibleSig3($p1, $p2 = null, $p3 = null) {}
function functionCompatibleSig4($p1, $p2 = null) {}
function functionCompatibleSig5($p1 = null, $p2) {}
function functionCompatibleSig6($p1 = null, $p2, $p3 = null, $p4 = null) {}
function functionIncompatibleSig1($p2, $p1 = null) {}

// Class used only for type hinting.
class kEvent {}

// Class, that will be made abstract.
abstract class ClassB {}

// Class, that will be made final.
final class ClassC {}

class ClassD {
	const SAME_CONST = 1;

	protected $publicPropertySr;
	private $publicToPrivatePropertySr;
	private $protectedPropertySr;

	public $publicPropertySame;
	protected $protectedPropertySame;
	private $privatePropertySame;

	public static $publicPropertyMs;
	protected static $protectedPropertyMs;
	private static $privatePropertyMs;

	public $publicPropertyMns;
	protected $protectedPropertyMns;
	private $privatePropertyMns;


	public function __construct() {}

	public function publicMethodSameEmpty() {}
	protected function protectedMethodSameEmpty() {}
	private function privateMethodSameEmpty() {}

	public function publicMethodSameNonEmpty($p1) {}
	protected function protectedMethodSameNonEmpty($p1) {}
	private function privateMethodSameNonEmpty($p1) {}

	protected function publicMethodSr() {}
	private function publicToPrivateMethodSr() {}
	private function protectedMethodSr() {}

	public abstract function publicMethodAb();
	protected abstract function protectedMethodAb();
	private abstract function privateMethodAb();

	public final function publicMethodFi() {}
	protected final function protectedMethodFi() {}
	private final function privateMethodFi() {}

	public static function publicMethodMs() {}
	protected static function protectedMethodMs() {}
	private static function privateMethodMs() {}

	public function publicMethodMns() {}
	protected function protectedMethodMns() {}
	private function privateMethodMns() {}

	public function publicMethodSiFromEmptyToNonEmpty($p1) {}
	protected function protectedMethodSiFromEmptyToNonEmpty($p1) {}
	private function privateMethodSiFromEmptyToNonEmpty($p1) {}

	public function publicMethodSiFromNonEmptyToEmpty() {}
	protected function protectedMethodSiFromNonEmptyToEmpty() {}
	private function privateMethodSiFromNonEmptyToEmpty() {}

	public function publicMethodSiFromNonEmptyToNonEmpty($p1, $p2) {}
	protected function protectedMethodSiFromNonEmptyToNonEmpty($p1, $p2) {}
	private function privateMethodSiFromNonEmptyToNonEmpty($p1, $p2) {}

	public function publicMethodCompatibleSig1($p1 = null) {}
	protected function protectedMethodCompatibleSig1($p1 = null) {}
	private function privateMethodCompatibleSig1($p1 = null) {}

	public function publicMethodCompatibleSig2($p1, $p2 = null) {}
	protected function protectedMethodCompatibleSig2($p1, $p2 = null) {}
	private function privateMethodCompatibleSig2($p1, $p2 = null) {}

	public function publicMethodCompatibleSig3($p1, $p2 = null, $p3 = null) {}
	protected function protectedMethodCompatibleSig3($p1, $p2 = null, $p3 = null) {}
	private function privateMethodCompatibleSig3($p1, $p2 = null, $p3 = null) {}

	public function publicMethodCompatibleSig4($p1, $p2 = null) {}
	protected function protectedMethodCompatibleSig4($p1, $p2 = null) {}
	private function privateMethodCompatibleSig4($p1, $p2 = null) {}

	public function publicMethodCompatibleSig5($p1 = null, $p2) {}
	protected function protectedMethodCompatibleSig5($p1 = null, $p2) {}
	private function privateMethodCompatibleSig5($p1 = null, $p2) {}

	public function publicMethodCompatibleSig6($p1 = null, $p2, $p3 = null, $p4 = null) {}
	protected function protectedMethodCompatibleSig6($p1 = null, $p2, $p3 = null, $p4 = null) {}
	private function privateMethodCompatibleSig6($p1 = null, $p2, $p3 = null, $p4 = null) {}

	public function publicMethodIncompatibleSig1($p2, $p1 = null) {}
	protected function protectedMethodIncompatibleSig1($p2, $p1 = null) {}
	private function privateMethodIncompatibleSig1($p2, $p1 = null) {}
}

// Only report BC breaks for class, where class member is declared.
class ClassE extends ClassD {}

// Renaming PHP5 into PHP4 constructor isn't a BC break.
class ClassF {
	public function ClassF() {}
}

// Class that stayed final isn't checked for changes in protected class members.
final class ClassG {
	const SAME_CONST = 1;

	protected $publicPropertySr;
	private $publicToPrivatePropertySr;
	private $protectedPropertySr;

	public $publicPropertySame;
	protected $protectedPropertySame;
	private $privatePropertySame;

	public static $publicPropertyMs;
	protected static $protectedPropertyMs;
	private static $privatePropertyMs;

	public $publicPropertyMns;
	protected $protectedPropertyMns;
	private $privatePropertyMns;


	public function __construct() {}

	public function publicMethodSameEmpty() {}
	protected function protectedMethodSameEmpty() {}
	private function privateMethodSameEmpty() {}

	public function publicMethodSameNonEmpty($p1) {}
	protected function protectedMethodSameNonEmpty($p1) {}
	private function privateMethodSameNonEmpty($p1) {}

	protected function publicMethodSr() {}
	private function publicToPrivateMethodSr() {}
	private function protectedMethodSr() {}

	public abstract function publicMethodAb();
	protected abstract function protectedMethodAb();
	private abstract function privateMethodAb();

	public final function publicMethodFi() {}
	protected final function protectedMethodFi() {}
	private final function privateMethodFi() {}

	public static function publicMethodMs() {}
	protected static function protectedMethodMs() {}
	private static function privateMethodMs() {}

	public function publicMethodMns() {}
	protected function protectedMethodMns() {}
	private function privateMethodMns() {}

	public function publicMethodSiFromEmptyToNonEmpty($p1) {}
	protected function protectedMethodSiFromEmptyToNonEmpty($p1) {}
	private function privateMethodSiFromEmptyToNonEmpty($p1) {}

	public function publicMethodSiFromNonEmptyToEmpty() {}
	protected function protectedMethodSiFromNonEmptyToEmpty() {}
	private function privateMethodSiFromNonEmptyToEmpty() {}

	public function publicMethodSiFromNonEmptyToNonEmpty($p1, $p2) {}
	protected function protectedMethodSiFromNonEmptyToNonEmpty($p1, $p2) {}
	private function privateMethodSiFromNonEmptyToNonEmpty($p1, $p2) {}

	public function publicMethodCompatibleSig1($p1 = null) {}
	protected function protectedMethodCompatibleSig1($p1 = null) {}
	private function privateMethodCompatibleSig1($p1 = null) {}

	public function publicMethodCompatibleSig2($p1, $p2 = null) {}
	protected function protectedMethodCompatibleSig2($p1, $p2 = null) {}
	private function privateMethodCompatibleSig2($p1, $p2 = null) {}

	public function publicMethodCompatibleSig3($p1, $p2 = null, $p3 = null) {}
	protected function protectedMethodCompatibleSig3($p1, $p2 = null, $p3 = null) {}
	private function privateMethodCompatibleSig3($p1, $p2 = null, $p3 = null) {}

	public function publicMethodCompatibleSig4($p1, $p2 = null) {}
	protected function protectedMethodCompatibleSig4($p1, $p2 = null) {}
	private function privateMethodCompatibleSig4($p1, $p2 = null) {}

	public function publicMethodCompatibleSig5($p1 = null, $p2) {}
	protected function protectedMethodCompatibleSig5($p1 = null, $p2) {}
	private function privateMethodCompatibleSig5($p1 = null, $p2) {}

	public function publicMethodCompatibleSig6($p1 = null, $p2, $p3 = null, $p4 = null) {}
	protected function protectedMethodCompatibleSig6($p1 = null, $p2, $p3 = null, $p4 = null) {}
	private function privateMethodCompatibleSig6($p1 = null, $p2, $p3 = null, $p4 = null) {}

	public function publicMethodIncompatibleSig1($p2, $p1 = null) {}
	protected function protectedMethodIncompatibleSig1($p2, $p1 = null) {}
	private function privateMethodIncompatibleSig1($p2, $p1 = null) {}
}

// Class, that was made final is checking for all usual stuff.
final class ClassH {
	const SAME_CONST = 1;

	protected $publicPropertySr;
	private $publicToPrivatePropertySr;
	private $protectedPropertySr;

	public $publicPropertySame;
	protected $protectedPropertySame;
	private $privatePropertySame;

	public static $publicPropertyMs;
	protected static $protectedPropertyMs;
	private static $privatePropertyMs;

	public $publicPropertyMns;
	protected $protectedPropertyMns;
	private $privatePropertyMns;

	public function __construct() {}

	public function publicMethodSameEmpty() {}
	protected function protectedMethodSameEmpty() {}
	private function privateMethodSameEmpty() {}

	public function publicMethodSameNonEmpty($p1) {}
	protected function protectedMethodSameNonEmpty($p1) {}
	private function privateMethodSameNonEmpty($p1) {}

	protected function publicMethodSr() {}
	private function publicToPrivateMethodSr() {}
	private function protectedMethodSr() {}

	public abstract function publicMethodAb();
	protected abstract function protectedMethodAb();
	private abstract function privateMethodAb();

	public final function publicMethodFi() {}
	protected final function protectedMethodFi() {}
	private final function privateMethodFi() {}

	public static function publicMethodMs() {}
	protected static function protectedMethodMs() {}
	private static function privateMethodMs() {}

	public function publicMethodMns() {}
	protected function protectedMethodMns() {}
	private function privateMethodMns() {}

	public function publicMethodSiFromEmptyToNonEmpty($p1) {}
	protected function protectedMethodSiFromEmptyToNonEmpty($p1) {}
	private function privateMethodSiFromEmptyToNonEmpty($p1) {}

	public function publicMethodSiFromNonEmptyToEmpty() {}
	protected function protectedMethodSiFromNonEmptyToEmpty() {}
	private function privateMethodSiFromNonEmptyToEmpty() {}

	public function publicMethodSiFromNonEmptyToNonEmpty($p1, $p2) {}
	protected function protectedMethodSiFromNonEmptyToNonEmpty($p1, $p2) {}
	private function privateMethodSiFromNonEmptyToNonEmpty($p1, $p2) {}

	public function publicMethodCompatibleSig1($p1 = null) {}
	protected function protectedMethodCompatibleSig1($p1 = null) {}
	private function privateMethodCompatibleSig1($p1 = null) {}

	public function publicMethodCompatibleSig2($p1, $p2 = null) {}
	protected function protectedMethodCompatibleSig2($p1, $p2 = null) {}
	private function privateMethodCompatibleSig2($p1, $p2 = null) {}

	public function publicMethodCompatibleSig3($p1, $p2 = null, $p3 = null) {}
	protected function protectedMethodCompatibleSig3($p1, $p2 = null, $p3 = null) {}
	private function privateMethodCompatibleSig3($p1, $p2 = null, $p3 = null) {}

	public function publicMethodCompatibleSig4($p1, $p2 = null) {}
	protected function protectedMethodCompatibleSig4($p1, $p2 = null) {}
	private function privateMethodCompatibleSig4($p1, $p2 = null) {}

	public function publicMethodCompatibleSig5($p1 = null, $p2) {}
	protected function protectedMethodCompatibleSig5($p1 = null, $p2) {}
	private function privateMethodCompatibleSig5($p1 = null, $p2) {}

	public function publicMethodCompatibleSig6($p1 = null, $p2, $p3 = null, $p4 = null) {}
	protected function protectedMethodCompatibleSig6($p1 = null, $p2, $p3 = null, $p4 = null) {}
	private function privateMethodCompatibleSig6($p1 = null, $p2, $p3 = null, $p4 = null) {}

	public function publicMethodIncompatibleSig1($p2, $p1 = null) {}
	protected function protectedMethodIncompatibleSig1($p2, $p1 = null) {}
	private function privateMethodIncompatibleSig1($p2, $p1 = null) {}
}

// In-Portal specifics.
class ExampleEventHandler
{
	protected function mapPermissions() {}

	protected function SetCustomQuery() {}

	protected function OnEventSr(kEvent $event) {}

	function OnEventSig1($event) {}

	function OnEventSig2(kEvent $event) {}

	function OnEventSig3(kEvent $event) {}
}

class AdminEventsHandler
{
	protected function mapPermissions() {}

	protected function SetCustomQuery() {}

	protected function OnEventSr(kEvent $event) {}

	function OnEventSig1($event) {}

	function OnEventSig2(kEvent $event) {}

	function OnEventSig3(kEvent $event) {}
}

class ExampleTagProcessor
{
	protected function TagNameOneSr(array $params) {}

	protected function TagNameTwoSr($params) {}

	protected function notTagNameOneSr(array $params) {}

	protected function notTagNameTwoSr($params) {}
}
