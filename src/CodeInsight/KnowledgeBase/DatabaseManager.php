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
	 * Database directory.
	 *
	 * @var string
	 */
	private $_databaseDirectory;

	/**
	 * Database manager constructor.
	 *
	 * @param MigrationManager $migration_manager Migration manager.
	 * @param string           $working_directory Working directory.
	 */
	public function __construct(MigrationManager $migration_manager, $working_directory)
	{
		$this->_migrationManager = $migration_manager;
		$this->_databaseDirectory = $working_directory . '/databases';

		if ( !file_exists($this->_databaseDirectory) ) {
			mkdir($this->_databaseDirectory);
		}
	}

	/**
	 * Returns db for given project.
	 *
	 * @param string      $project_path Project path.
	 * @param string|null $fork         Fork name.
	 *
	 * @return ExtendedPdoInterface
	 * @throws \InvalidArgumentException When relative project path is given.
	 */
	public function getDatabase($project_path, $fork = null)
	{
		if ( strpos($project_path, '/') !== 0 ) {
			throw new \InvalidArgumentException('The "$project_path" argument must contain absolute path.');
		}

		$project_path = $this->_databaseDirectory . $project_path;

		if ( !file_exists($project_path) ) {
			mkdir($project_path, 0777, true);
		}

		$db_file = $project_path . '/code_insight.sqlite';

		if ( empty($fork) ) {
			return new ExtendedPdo('sqlite:' . $db_file);
		}

		$fork_db_file = $project_path . '/code_insight-' . $fork . '.sqlite';

		if ( !file_exists($fork_db_file) && file_exists($db_file) ) {
			copy($db_file, $fork_db_file);
		}

		return new ExtendedPdo('sqlite:' . $fork_db_file);
	}

	/**
	 * Returns forks for given project.
	 *
	 * @param string $project_path Project path.
	 *
	 * @return string[]
	 * @throws \InvalidArgumentException When relative project path is given.
	 */
	public function getForks($project_path)
	{
		if ( strpos($project_path, '/') !== 0 ) {
			throw new \InvalidArgumentException('The "$project_path" argument must contain absolute path.');
		}

		$project_path = $this->_databaseDirectory . $project_path;

		if ( !file_exists($project_path) ) {
			return array();
		}

		$ret = array();
		$absolute_forks = glob($project_path . '/code_insight-*.sqlite');

		foreach ( $absolute_forks as $absolute_fork ) {
			$ret[] = preg_replace(
				'/^' . preg_quote($project_path . '/code_insight-', '/') . '(.+).sqlite/',
				'$1',
				$absolute_fork
			);
		}

		return $ret;
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
