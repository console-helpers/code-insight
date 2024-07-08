<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace Tests\ConsoleHelpers\CodeInsight\KnowledgeBase;


use Aura\Sql\ExtendedPdo;
use ConsoleHelpers\CodeInsight\KnowledgeBase\DatabaseManager;
use Prophecy\Prophecy\ObjectProphecy;
use Tests\ConsoleHelpers\ConsoleKit\WorkingDirectoryAwareTestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;
use ConsoleHelpers\DatabaseMigration\MigrationManager;
use ConsoleHelpers\DatabaseMigration\MigrationContext;

class DatabaseManagerTest extends WorkingDirectoryAwareTestCase
{

    use AssertionRenames;

	/**
	 * Working directory.
	 *
	 * @var string
	 */
	protected $workingDirectory;

	/**
	 * Migration manager.
	 *
	 * @var ObjectProphecy
	 */
	protected $migrationManager;

    /**
     * @before
     */
	public function setUpTest()
	{
		parent::setUpTest();

		$this->workingDirectory = $this->getWorkingDirectory();
		$this->migrationManager = $this->prophesize(MigrationManager::class);
	}

	public function testTheDatabasesFolderIsCreated()
	{
		$this->getDatabaseManager();

		$this->assertFileExists($this->workingDirectory . '/databases');
	}

	public function testRelativeProjectPathError()
	{
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "$project_path" argument must contain absolute path.');

		$this->getDatabaseManager()->getDatabase('relative/path');
	}

	public function testCreatingDatabase()
	{
		$database_manager = $this->getDatabaseManager();
		$database = $database_manager->getDatabase('/absolute/path');

		$this->assertFileExists($this->workingDirectory . '/databases/absolute/path');
		$this->assertFileDoesNotExist($this->workingDirectory . '/databases/absolute/path/code_insight.sqlite');
		$this->assertEquals(
			'sqlite:' . $this->workingDirectory . '/databases/absolute/path/code_insight.sqlite',
			$this->getDSN($database)
		);
	}

	public function testCreatingForkedDatabaseFromNothing()
	{
		$database_manager = $this->getDatabaseManager();
		$database = $database_manager->getDatabase('/absolute/path', 'fork');

		$this->assertFileExists($this->workingDirectory . '/databases/absolute/path');
		$this->assertFileDoesNotExist($this->workingDirectory . '/databases/absolute/path/code_insight-fork.sqlite');
		$this->assertEquals(
			'sqlite:' . $this->workingDirectory . '/databases/absolute/path/code_insight-fork.sqlite',
			$this->getDSN($database)
		);
	}

	public function testCreatingForkedDatabaseFromOriginal()
	{
		$database_manager = $this->getDatabaseManager();
		$original_database = $database_manager->getDatabase('/absolute/path');
		$original_database->perform('CREATE TABLE "SampleTable" ("Name" TEXT(255,0) NOT NULL, PRIMARY KEY("Name"))');

		$database = $database_manager->getDatabase('/absolute/path', 'fork');

		$sql = "SELECT name
				FROM sqlite_master
				WHERE type = 'table' AND name = :table_name";
		$found_table_name = $database->fetchValue($sql, array('table_name' => 'SampleTable'));

		$this->assertEquals('SampleTable', $found_table_name);

		$this->assertFileExists($this->workingDirectory . '/databases/absolute/path');
		$this->assertFileExists($this->workingDirectory . '/databases/absolute/path/code_insight-fork.sqlite');
		$this->assertEquals(
			'sqlite:' . $this->workingDirectory . '/databases/absolute/path/code_insight-fork.sqlite',
			$this->getDSN($database)
		);
	}

	/**
	 * Returns database DSN.
	 *
	 * @param ExtendedPdo $database Datbase.
	 *
	 * @return string
	 */
	protected function getDSN(ExtendedPdo $database)
	{
		// Aura.Sql 2.5.
		if ( method_exists($database, 'getDsn') ) {
			return $database->getDsn();
		}

		// Aura.Sql 3.0+.
		$debug_info = $database->__debugInfo();

		return $debug_info['args']['0'];
	}

	public function testRunMigrations()
	{
		$context = $this->prophesize(MigrationContext::class)->reveal();
		$this->migrationManager->run($context)->shouldBeCalled();

		$this->getDatabaseManager()->runMigrations($context);
	}

	/**
	 * Creates instance of database manager.
	 *
	 * @return DatabaseManager
	 */
	protected function getDatabaseManager()
	{
		return new DatabaseManager($this->migrationManager->reveal(), $this->workingDirectory);
	}

}
