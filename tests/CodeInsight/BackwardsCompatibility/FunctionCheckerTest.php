<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace Tests\ConsoleHelpers\CodeInsight\BackwardsCompatibility;


use ConsoleHelpers\CodeInsight\BackwardsCompatibility\AbstractChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\FunctionChecker;

class FunctionCheckerTest extends AbstractCheckerTestCase
{

	public function testGetName()
	{
		$this->assertEquals('function', $this->checker->getName());
	}

	public function testCheck()
	{
		$this->assertSame(
			array(
				array(
					'type' => FunctionChecker::TYPE_FUNCTION_DELETED,
					'element' => 'functionA',
				),
				array(
					'type' => FunctionChecker::TYPE_FUNCTION_SIGNATURE_CHANGED,
					'element' => 'functionSiFromEmptyToNonEmpty',
					'old' => '',
					'new' => '$p1',
				),
				array(
					'type' => FunctionChecker::TYPE_FUNCTION_SIGNATURE_CHANGED,
					'element' => 'functionSiFromNonEmptyToEmpty',
					'old' => '$p1',
					'new' => '',
				),
				array(
					'type' => FunctionChecker::TYPE_FUNCTION_SIGNATURE_CHANGED,
					'element' => 'functionSiFromNonEmptyToNonEmpty',
					'old' => '$p1',
					'new' => '$p1, $p2',
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
		return new FunctionChecker($this->cache);
	}

}
