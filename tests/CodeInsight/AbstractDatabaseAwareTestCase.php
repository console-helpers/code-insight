<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace Tests\ConsoleHelpers\CodeInsight;


use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use ConsoleHelpers\CodeInsight\Container;
use ConsoleHelpers\DatabaseMigration\MigrationContext;
use Tests\ConsoleHelpers\ConsoleKit\AbstractTestCase;

abstract class AbstractDatabaseAwareTestCase extends AbstractTestCase
{

	/**
	 * Database.
	 *
	 * @var ExtendedPdoInterface
	 */
	protected $database;

    /**
     * @before
     */
	public function setUpTest()
	{
		$this->database = self::createDatabase();
	}

	/**
	 * Checks, that database table is empty.
	 *
	 * @param array                $table_names Table names.
	 * @param ExtendedPdoInterface $db          Database.
	 *
	 * @return void
	 */
	protected function assertTablesEmpty(array $table_names, ExtendedPdoInterface $db = null)
	{
		foreach ( $table_names as $table_name ) {
			$this->assertTableCount($table_name, 0, $db);
		}
	}

	/**
	 * Checks, that database table is empty.
	 *
	 * @param string               $table_name Table name.
	 * @param ExtendedPdoInterface $db         Database.
	 *
	 * @return void
	 */
	protected function assertTableEmpty($table_name, ExtendedPdoInterface $db = null)
	{
		$this->assertTableCount($table_name, 0, $db);
	}

	/**
	 * Checks, table content.
	 *
	 * @param string               $table_name       Table name.
	 * @param array                $expected_content Expected content.
	 * @param ExtendedPdoInterface $db               Database.
	 *
	 * @return void
	 */
	protected function assertTableContent($table_name, array $expected_content, ExtendedPdoInterface $db = null)
	{
		$this->assertSame(
			$expected_content,
			$this->_dumpTable($table_name, $db),
			'Table "' . $table_name . '" content isn\'t correct.'
		);
	}

	/**
	 * Returns contents of the table.
	 *
	 * @param string               $table_name Table name.
	 * @param ExtendedPdoInterface $db         Database.
	 *
	 * @return array
	 */
	private function _dumpTable($table_name, ExtendedPdoInterface $db = null)
	{
		$db = $db ?: $this->database;

		$sql = 'SELECT *
				FROM ' . $table_name;
		$table_content = $db->fetchAll($sql);

		return $table_content;
	}

	/**
	 * Checks, that database table contains given number of records.
	 *
	 * @param string               $table_name            Table name.
	 * @param integer              $expected_record_count Expected record count.
	 * @param ExtendedPdoInterface $db                    Database.
	 *
	 * @return void
	 */
	protected function assertTableCount($table_name, $expected_record_count, ExtendedPdoInterface $db = null)
	{
		$db = $db ?: $this->database;

		$sql = 'SELECT COUNT(*)
				FROM ' . $table_name;
		$actual_record_count = $db->fetchValue($sql);

		$this->assertEquals(
			$expected_record_count,
			$actual_record_count,
			'The "' . $table_name . '" table contains ' . $expected_record_count . ' records'
		);
	}

	/**
	 * Creates database for testing with correct db structure.
	 *
	 * @return ExtendedPdoInterface
	 */
	protected static function createDatabase()
	{
		$db = new ExtendedPdo('sqlite::memory:');

		$container = new Container();
		$migration_manager = $container['migration_manager'];
		$migration_manager->run(new MigrationContext($db));

		return $db;
	}

}
