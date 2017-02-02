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


use Aura\Sql\ExtendedPdoInterface;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\AbstractChecker;
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

	protected function setUp()
	{
		parent::setUp();

		$cache = $this->prophesize('\Doctrine\Common\Cache\CacheProvider');
		$cache->fetch(Argument::any())->willReturn(false);
		$cache->save(Argument::cetera())->willReturn(true);

		$this->cache = $cache->reveal();

		if ( !isset(static::$oldKnowledgeBase) ) {
			static::$oldKnowledgeBase = new KnowledgeBase(__DIR__ . '/fixtures/OldProject', $this->createDatabase());
			static::$oldKnowledgeBase->silentRefresh();
		}

		if ( !isset(static::$newKnowledgeBase) ) {
			static::$newKnowledgeBase = new KnowledgeBase(__DIR__ . '/fixtures/NewProject', $this->createDatabase());
			static::$newKnowledgeBase->silentRefresh();
		}

		$this->checker = $this->createChecker();
	}

	public function testEmptyCheck()
	{
		$this->assertEmpty(
			$this->checker->check(self::$oldKnowledgeBase->getDatabase(), self::$oldKnowledgeBase->getDatabase())
		);
	}

	/**
	 * Creates checker.
	 *
	 * @return AbstractChecker
	 */
	abstract protected function createChecker();

}
