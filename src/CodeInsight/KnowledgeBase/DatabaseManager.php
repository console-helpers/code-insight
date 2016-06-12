<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\KnowledgeBase;


use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use ConsoleHelpers\DatabaseMigration\MigrationManager;
use ConsoleHelpers\DatabaseMigration\MigrationContext;

class DatabaseManager
{

	/**
	 * Migration manager.
	 *
	 * @var MigrationManager
	 */
	private $_migrationManager;

	/**
	 * Database manager constructor.
	 *
	 * @param MigrationManager $migration_manager Migration manager.
	 */
	public function __construct(MigrationManager $migration_manager)
	{
		$this->_migrationManager = $migration_manager;
	}

	/**
	 * Returns db for given repository.
	 *
	 * @param string $project_path Project path.
	 *
	 * @return ExtendedPdoInterface
	 */
	public function getDatabase($project_path)
	{
		return new ExtendedPdo('sqlite:' . $project_path . '/code_insight.sqlite');
	}

	/**
	 * Runs outstanding migrations on the database.
	 *
	 * @param MigrationContext $context Context.
	 *
	 * @return void
	 */
	public function runMigrations(MigrationContext $context)
	{
		$this->_migrationManager->run($context);
	}

}
