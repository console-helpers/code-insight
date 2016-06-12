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


use ConsoleHelpers\ConsoleKit\ConsoleIO;
use ConsoleHelpers\DatabaseMigration\MigrationContext;

class KnowledgeBaseFactory
{

	/**
	 * Database manager.
	 *
	 * @var DatabaseManager
	 */
	private $_databaseManager;

	/**
	 * Create knowledge base factory instance.
	 *
	 * @param DatabaseManager $database_manager Database manager.
	 */
	public function __construct(DatabaseManager $database_manager)
	{
		$this->_databaseManager = $database_manager;
	}

	/**
	 * Returns knowledge base for project path.
	 *
	 * @param string    $project_path Project path.
	 * @param ConsoleIO $io           Console IO.
	 *
	 * @return KnowledgeBase
	 */
	public function getKnowledgeBase($project_path, ConsoleIO $io = null)
	{
		// Gets database for given project path.
		$database = $this->_databaseManager->getDatabase($project_path);

		// Create blank revision log.
		$knowledge_base = new KnowledgeBase($project_path, $database, $io);

		// Run migrations (includes initial schema creation).
		$context = new MigrationContext($database);
		$this->_databaseManager->runMigrations($context);

		return $knowledge_base;
	}

}
