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
use ConsoleHelpers\CodeInsight\KnowledgeBase\KnowledgeBase;
use Doctrine\Common\Cache\CacheProvider;
use Prophecy\Argument;
use Tests\ConsoleHelpers\CodeInsight\AbstractDatabaseAwareTestCase;

abstract class AbstractCheckerTestCase extends AbstractDatabaseAwareTestCase
{

	/**
	 * Cache.
	 *
	 * @var CacheProvider
	 */
	protected $cache;

	/**
	 * Checker.
	 *
	 * @var AbstractChecker
	 */
	protected $checker;

	/**
	 * Old database.
	 *
	 * @var KnowledgeBase
	 */
	protected static $oldKnowledgeBase;

	/**
	 * New database.
	 *
	 * @var KnowledgeBase
	 */
	protected static $newKnowledgeBase;

    /**
     * @beforeClass
     */
	public static function setUpBeforeClassTest()
	{
		static::$oldKnowledgeBase = new KnowledgeBase(__DIR__ . '/fixtures/OldProject', static::createDatabase());
		static::$oldKnowledgeBase->silentRefresh();

		static::$newKnowledgeBase = new KnowledgeBase(__DIR__ . '/fixtures/NewProject', static::createDatabase());
		static::$newKnowledgeBase->silentRefresh();
	}

    /**
     * @before
     */
	public function setUpTest()
	{
		parent::setUpTest();

		$cache = $this->prophesize(CacheProvider::class);
		$cache->fetch(Argument::any())->willReturn(false);
		$cache->save(Argument::cetera())->willReturn(true);

		$this->cache = $cache->reveal();
		$this->checker = $this->createChecker();
	}

	public function testEmptyCheck()
	{
		$this->assertEmpty(
			$this->checker->check(static::$oldKnowledgeBase->getDatabase(), static::$oldKnowledgeBase->getDatabase())
		);
	}

	/**
	 * Asserts that two arrays are equal ignoring element order.
	 *
	 * @param array  $expected Expected.
	 * @param array  $actual   Actual.
	 * @param string $message  Message.
	 *
	 * @return void
	 */
	protected function assertArrayEquals(array $expected, array $actual, $message = '')
	{
		$this->assertEquals($expected, $actual, $message, 0.0, 10, true);
	}

	/**
	 * Creates checker.
	 *
	 * @return AbstractChecker
	 */
	abstract protected function createChecker();

}
