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


use ConsoleHelpers\CodeInsight\KnowledgeBase\DatabaseManager;
use Prophecy\Prophecy\ObjectProphecy;
use Tests\ConsoleHelpers\ConsoleKit\WorkingDirectoryAwareTestCase;

class DatabaseManagerTest extends WorkingDirectoryAwareTestCase
{

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

	protected function setUp()
	{
		parent::setUp();

		$this->workingDirectory = $this->getWorkingDirectory();
		$this->migrationManager = $this->prophesize('\ConsoleHelpers\DatabaseMigration\MigrationManager');
	}

	public function testTheDatabasesFolderIsCreated()
	{
		$this->getDatabaseManager();

		$this->assertFileExists($this->workingDirectory . '/databases');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage The "$project_path" argument must contain absolute path.
	 */
	public function testRelativeProjectPathError()
	{
		$this->getDatabaseManager()->getDatabase('relative/path');
	}

	public function testCreatingDatabase()
	{
		$database_manager = $this->getDatabaseManager();
		$database = $database_manager->getDatabase('/absolute/path');

		$this->assertFileExists($this->workingDirectory . '/databases/absolute/path');
		$this->assertFileNotExists($this->workingDirectory . '/databases/absolute/path/code_insight.sqlite');
		$this->assertEquals(
			'sqlite:' . $this->workingDirectory . '/databases/absolute/path/code_insight.sqlite',
			$database->getDsn()
		);
	}

	public function testCreatingForkedDatabaseFromNothing()
	{
		$database_manager = $this->getDatabaseManager();
		$database = $database_manager->getDatabase('/absolute/path', 'fork');

		$this->assertFileExists($this->workingDirectory . '/databases/absolute/path');
		$this->assertFileNotExists($this->workingDirectory . '/databases/absolute/path/code_insight-fork.sqlite');
		$this->assertEquals(
			'sqlite:' . $this->workingDirectory . '/databases/absolute/path/code_insight-fork.sqlite',
			$database->getDsn()
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
			$database->getDsn()
		);
	}

	public function testRunMigrations()
	{
		$context = $this->prophesize('\ConsoleHelpers\DatabaseMigration\MigrationContext')->reveal();
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
