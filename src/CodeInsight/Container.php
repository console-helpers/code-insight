<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight;


use ConsoleHelpers\CodeInsight\BackwardsCompatibility\CheckerFactory;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\ClassChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\ConstantChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\FunctionChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\InPortalClassChecker;
use ConsoleHelpers\CodeInsight\KnowledgeBase\DatabaseManager;
use ConsoleHelpers\CodeInsight\KnowledgeBase\KnowledgeBaseFactory;
use ConsoleHelpers\DatabaseMigration\MigrationManager;
use ConsoleHelpers\DatabaseMigration\PhpMigrationRunner;
use ConsoleHelpers\DatabaseMigration\SqlMigrationRunner;

class Container extends \ConsoleHelpers\ConsoleKit\Container
{

	/**
	 * {@inheritdoc}
	 */
	public function __construct(array $values = array())
	{
		parent::__construct($values);

		$this['app_name'] = 'Code-Insight';
		$this['app_version'] = '@git-version@';

		$this['working_directory_sub_folder'] = '.code-insight';

		$this['config_defaults'] = array();

		$this['project_root_folder'] = function () {
			return dirname(dirname(__DIR__));
		};

		$this['migration_manager'] = function ($c) {
			$migrations_directory = $c['project_root_folder'] . '/migrations';
			$migration_manager = new MigrationManager($migrations_directory, $c);
			$migration_manager->registerMigrationRunner(new SqlMigrationRunner());
			$migration_manager->registerMigrationRunner(new PhpMigrationRunner());

			return $migration_manager;
		};

		$this['db_manager'] = function ($c) {
			return new DatabaseManager($c['migration_manager']);
		};

		$this['knowledge_base_factory'] = function ($c) {
			return new KnowledgeBaseFactory($c['db_manager']);
		};

		$this['bc_checker_factory'] = function ($c) {
			$factory = new CheckerFactory();
			$factory->add(new ClassChecker());
			$factory->add(new FunctionChecker());
			$factory->add(new ConstantChecker());

			$factory->add(new InPortalClassChecker());

			return $factory;
		};
	}

}
