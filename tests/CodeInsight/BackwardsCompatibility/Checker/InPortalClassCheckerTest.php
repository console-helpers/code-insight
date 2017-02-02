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
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\InPortalClassChecker;

class InPortalClassCheckerTest extends AbstractCheckerTestCase
{

	public function testGetName()
	{
		$this->assertEquals('inportal_class', $this->checker->getName());
	}

	public function testCheck()
	{
		$this->assertSame(
			array(
				array(
					'type' => InPortalClassChecker::TYPE_CLASS_DELETED,
					'element' => 'ClassA',
				),
				array(
					'type' => InPortalClassChecker::TYPE_CLASS_DELETED,
					'element' => 'InterfaceA',
				),
				array(
					'type' => InPortalClassChecker::TYPE_CLASS_DELETED,
					'element' => 'TraitA',
				),
				array(
					'type' => InPortalClassChecker::TYPE_CLASS_MADE_ABSTRACT,
					'element' => 'ClassB',
				),
				array(
					'type' => InPortalClassChecker::TYPE_CLASS_MADE_FINAL,
					'element' => 'ClassC',
				),
				array(
					'type' => InPortalClassChecker::TYPE_CLASS_CONSTANT_DELETED,
					'element' => 'ClassD::SOME_CONST',
				),
				array(
					'type' => InPortalClassChecker::TYPE_PROPERTY_DELETED,
					'element' => 'ClassD::$protectedProperty',
				),
				array(
					'type' => InPortalClassChecker::TYPE_PROPERTY_SCOPE_REDUCED,
					'element' => 'ClassD::$protectedPropertySr',
					'old' => 'protected',
					'new' => 'private',
				),
				array(
					'type' => InPortalClassChecker::TYPE_PROPERTY_DELETED,
					'element' => 'ClassD::$publicProperty',
				),
				array(
					'type' => InPortalClassChecker::TYPE_PROPERTY_SCOPE_REDUCED,
					'element' => 'ClassD::$publicPropertySr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => InPortalClassChecker::TYPE_PROPERTY_SCOPE_REDUCED,
					'element' => 'ClassD::$publicToPrivatePropertySr',
					'old' => 'public',
					'new' => 'private',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_DELETED,
					'element' => 'ClassD::protectedMethod',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_MADE_ABSTRACT,
					'element' => 'ClassD::protectedMethodAb',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_MADE_FINAL,
					'element' => 'ClassD::protectedMethodFi',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::protectedMethodSiFromEmptyToNonEmpty',
					'old' => '',
					'new' => '$p1',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::protectedMethodSiFromNonEmptyToEmpty',
					'old' => '$p1',
					'new' => '',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::protectedMethodSiFromNonEmptyToNonEmpty',
					'old' => '$p1',
					'new' => '$p1, $p2',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ClassD::protectedMethodSr',
					'old' => 'protected',
					'new' => 'private',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_DELETED,
					'element' => 'ClassD::publicMethod',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_MADE_ABSTRACT,
					'element' => 'ClassD::publicMethodAb',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_MADE_FINAL,
					'element' => 'ClassD::publicMethodFi',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::publicMethodSiFromEmptyToNonEmpty',
					'old' => '',
					'new' => '$p1',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::publicMethodSiFromNonEmptyToEmpty',
					'old' => '$p1',
					'new' => '',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_SIGNATURE_CHANGED,
					'element' => 'ClassD::publicMethodSiFromNonEmptyToNonEmpty',
					'old' => '$p1',
					'new' => '$p1, $p2',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ClassD::publicMethodSr',
					'old' => 'public',
					'new' => 'protected',
				),
				array(
					'type' => InPortalClassChecker::TYPE_METHOD_SCOPE_REDUCED,
					'element' => 'ClassD::publicToPrivateMethodSr',
					'old' => 'public',
					'new' => 'private',
				),
				array(
					'type' => InPortalClassChecker::TYPE_CLASS_CONSTANT_DELETED,
					'element' => 'ClassE::SOME_CONST',
				),
			),
			$this->checker->check(self::$oldKnowledgeBase->getDatabase(), self::$newKnowledgeBase->getDatabase())
		);
	}

	/**
	 * Creates checker.
	 *
	 * @return AbstractChecker
	 */
	protected function createChecker()
	{
		return new InPortalClassChecker($this->cache);
	}

}
