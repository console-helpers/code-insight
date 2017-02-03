<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace Tests\ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker;


use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\AbstractChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\ClassChecker;

class ClassCheckerTest extends AbstractCheckerTestCase
{

	public function testGetName()
	{
		$this->assertEquals('class', $this->checker->getName());
	}

	public function testCheck()
	{
		$this->assertEquals(
			array(
				array(
					'type' => ClassChecker::TYPE_CLASS_DELETED,
					'element' => 'ClassA',
				),
				array(
					'type' => ClassChecker::TYPE_CLASS_DELETED,
					'element' => 'InterfaceA',
				),
				array(
					'type' => ClassChecker::TYPE_CLASS_DELETED,
					'element' => 'TraitA',
				),
				array(
					'type' => ClassChecker::TYPE_CLASS_MADE_ABSTRACT,
					'element' => 'ClassB',
				),
				array(
					'type' => ClassChecker::TYPE_CLASS_MADE_FINAL,
					'element' => 'ClassC',
				),
				array(
					'type' => ClassChecker::TYPE_CLASS_CONSTANT_DELETED,
					'element' => 'ClassD::SOME_CONST',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_DELETED,
					'element' => 'ClassD::$protectedProperty',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_MADE_NON_STATIC,
					'element' => 'ClassD::$protectedPropertyMns',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_MADE_STATIC,
					'element' => 'ClassD::$protectedPropertyMs',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_SCOPE_REDUCED,
					'element' => 'ClassD::$protectedPropertySr',
					'old' => 'protected',
					'new' => 'private',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_DELETED,
					'element' => 'ClassD::$publicProperty',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_MADE_NON_STATIC,
					'element' => 'ClassD::$publicPropertyMns',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_MADE_STATIC,
					'element' => 'ClassD::$publicPropertyMs',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_SCOPE_REDUCED,
					'element' => 'ClassD::$publicPropertySr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_SCOPE_REDUCED,
					'element' => 'ClassD::$publicToPrivatePropertySr',
					'old' => 'public',
					'new' => 'private',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_DELETED,
					'element' => 'ClassD::protectedMethod',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_ABSTRACT,
					'element' => 'ClassD::protectedMethodAb',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_FINAL,
					'element' => 'ClassD::protectedMethodFi',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::protectedMethodIncompatibleSig1',
					'old' => '$p1',
					'new' => '$p2, $p1 = NULL',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_NON_STATIC,
					'element' => 'ClassD::protectedMethodMns',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_STATIC,
					'element' => 'ClassD::protectedMethodMs',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::protectedMethodSiFromEmptyToNonEmpty',
					'old' => '',
					'new' => '$p1',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::protectedMethodSiFromNonEmptyToEmpty',
					'old' => '$p1',
					'new' => '',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::protectedMethodSiFromNonEmptyToNonEmpty',
					'old' => '$p1',
					'new' => '$p1, $p2',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ClassD::protectedMethodSr',
					'old' => 'protected',
					'new' => 'private',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_DELETED,
					'element' => 'ClassD::publicMethod',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_ABSTRACT,
					'element' => 'ClassD::publicMethodAb',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_FINAL,
					'element' => 'ClassD::publicMethodFi',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::publicMethodIncompatibleSig1',
					'old' => '$p1',
					'new' => '$p2, $p1 = NULL',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_NON_STATIC,
					'element' => 'ClassD::publicMethodMns',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_STATIC,
					'element' => 'ClassD::publicMethodMs',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::publicMethodSiFromEmptyToNonEmpty',
					'old' => '',
					'new' => '$p1',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::publicMethodSiFromNonEmptyToEmpty',
					'old' => '$p1',
					'new' => '',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::publicMethodSiFromNonEmptyToNonEmpty',
					'old' => '$p1',
					'new' => '$p1, $p2',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ClassD::publicMethodSr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ClassD::publicToPrivateMethodSr',
					'old' => 'public',
					'new' => 'private',
				),
				array(
					'type' => ClassChecker::TYPE_CLASS_CONSTANT_DELETED,
					'element' => 'ClassE::SOME_CONST',
				),
				array(
					'type' => ClassChecker::TYPE_CLASS_CONSTANT_DELETED,
					'element' => 'ClassG::SOME_CONST',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_DELETED,
					'element' => 'ClassG::$publicProperty',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_MADE_NON_STATIC,
					'element' => 'ClassG::$publicPropertyMns',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_MADE_STATIC,
					'element' => 'ClassG::$publicPropertyMs',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_SCOPE_REDUCED,
					'element' => 'ClassG::$publicPropertySr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_SCOPE_REDUCED,
					'element' => 'ClassG::$publicToPrivatePropertySr',
					'old' => 'public',
					'new' => 'private',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_DELETED,
					'element' => 'ClassG::publicMethod',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_ABSTRACT,
					'element' => 'ClassG::publicMethodAb',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_FINAL,
					'element' => 'ClassG::publicMethodFi',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassG::publicMethodIncompatibleSig1',
					'old' => '$p1',
					'new' => '$p2, $p1 = NULL',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_NON_STATIC,
					'element' => 'ClassG::publicMethodMns',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_STATIC,
					'element' => 'ClassG::publicMethodMs',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassG::publicMethodSiFromEmptyToNonEmpty',
					'old' => '',
					'new' => '$p1',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassG::publicMethodSiFromNonEmptyToEmpty',
					'old' => '$p1',
					'new' => '',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassG::publicMethodSiFromNonEmptyToNonEmpty',
					'old' => '$p1',
					'new' => '$p1, $p2',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ClassG::publicMethodSr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ClassG::publicToPrivateMethodSr',
					'old' => 'public',
					'new' => 'private',
				),
				array(
					'type' => ClassChecker::TYPE_CLASS_MADE_FINAL,
					'element' => 'ClassH',
				),
				array(
					'type' => ClassChecker::TYPE_CLASS_CONSTANT_DELETED,
					'element' => 'ClassH::SOME_CONST',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_DELETED,
					'element' => 'ClassH::$protectedProperty',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_MADE_NON_STATIC,
					'element' => 'ClassH::$protectedPropertyMns',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_MADE_STATIC,
					'element' => 'ClassH::$protectedPropertyMs',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_SCOPE_REDUCED,
					'element' => 'ClassH::$protectedPropertySr',
					'old' => 'protected',
					'new' => 'private',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_DELETED,
					'element' => 'ClassH::$publicProperty',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_MADE_NON_STATIC,
					'element' => 'ClassH::$publicPropertyMns',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_MADE_STATIC,
					'element' => 'ClassH::$publicPropertyMs',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_SCOPE_REDUCED,
					'element' => 'ClassH::$publicPropertySr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_PROPERTY_SCOPE_REDUCED,
					'element' => 'ClassH::$publicToPrivatePropertySr',
					'old' => 'public',
					'new' => 'private',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_DELETED,
					'element' => 'ClassH::protectedMethod',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_ABSTRACT,
					'element' => 'ClassH::protectedMethodAb',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_FINAL,
					'element' => 'ClassH::protectedMethodFi',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassH::protectedMethodIncompatibleSig1',
					'old' => '$p1',
					'new' => '$p2, $p1 = NULL',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_NON_STATIC,
					'element' => 'ClassH::protectedMethodMns',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_STATIC,
					'element' => 'ClassH::protectedMethodMs',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassH::protectedMethodSiFromEmptyToNonEmpty',
					'old' => '',
					'new' => '$p1',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassH::protectedMethodSiFromNonEmptyToEmpty',
					'old' => '$p1',
					'new' => '',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassH::protectedMethodSiFromNonEmptyToNonEmpty',
					'old' => '$p1',
					'new' => '$p1, $p2',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ClassH::protectedMethodSr',
					'old' => 'protected',
					'new' => 'private',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_DELETED,
					'element' => 'ClassH::publicMethod',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_ABSTRACT,
					'element' => 'ClassH::publicMethodAb',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_FINAL,
					'element' => 'ClassH::publicMethodFi',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassH::publicMethodIncompatibleSig1',
					'old' => '$p1',
					'new' => '$p2, $p1 = NULL',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_NON_STATIC,
					'element' => 'ClassH::publicMethodMns',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_MADE_STATIC,
					'element' => 'ClassH::publicMethodMs',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassH::publicMethodSiFromEmptyToNonEmpty',
					'old' => '',
					'new' => '$p1',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassH::publicMethodSiFromNonEmptyToEmpty',
					'old' => '$p1',
					'new' => '',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassH::publicMethodSiFromNonEmptyToNonEmpty',
					'old' => '$p1',
					'new' => '$p1, $p2',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ClassH::publicMethodSr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ClassH::publicToPrivateMethodSr',
					'old' => 'public',
					'new' => 'private',
				),
				// In-Portal specifics.
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ExampleEventHandler::OnEventSig1',
					'old' => '&$event',
					'new' => '$event',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ExampleEventHandler::OnEventSig2',
					'old' => '&$event',
					'new' => '\kEvent $event',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ExampleEventHandler::OnEventSig3',
					'old' => '$event',
					'new' => '\kEvent $event',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ExampleEventHandler::OnEventSr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ExampleEventHandler::SetCustomQuery',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ExampleEventHandler::mapPermissions',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'AdminEventsHandler::OnEventSig1',
					'old' => '&$event',
					'new' => '$event',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'AdminEventsHandler::OnEventSig2',
					'old' => '&$event',
					'new' => '\kEvent $event',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'AdminEventsHandler::OnEventSig3',
					'old' => '$event',
					'new' => '\kEvent $event',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'AdminEventsHandler::OnEventSr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'AdminEventsHandler::SetCustomQuery',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'AdminEventsHandler::mapPermissions',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ExampleTagProcessor::TagNameOneSr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ExampleTagProcessor::TagNameTwoSr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ExampleTagProcessor::notTagNameOneSr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => ClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ExampleTagProcessor::notTagNameTwoSr',
					'old' => 'public',
					'new' => 'protected',
				),
			),
			$this->checker->check(static::$oldKnowledgeBase->getDatabase(), static::$newKnowledgeBase->getDatabase())
		);
	}

	/**
	 * Creates checker.
	 *
	 * @return AbstractChecker
	 */
	protected function createChecker()
	{
		return new ClassChecker($this->cache);
	}

}
