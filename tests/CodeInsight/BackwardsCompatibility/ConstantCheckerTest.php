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
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\ConstantChecker;

class ConstantCheckerTest extends AbstractCheckerTestCase
{

	public function testGetName()
	{
		$this->assertEquals('constant', $this->checker->getName());
	}

	public function testCheck()
	{
		$this->assertSame(
			array(
				array(
					'type' => ConstantChecker::TYPE_CONSTANT_DELETED,
					'element' => 'SOME_CONST',
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
		return new ConstantChecker($this->cache);
	}

}
