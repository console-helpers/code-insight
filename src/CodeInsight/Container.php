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


use ConsoleHelpers\CodeInsight\BackwardsCompatibility\BreakFilter;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\CheckerFactory;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\ClassChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\ConstantChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\FunctionChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\InPortalClassChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Reporter\HtmlReporter;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Reporter\JsonReporter;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Reporter\ReporterFactory;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Reporter\TextReporter;
use ConsoleHelpers\CodeInsight\Cache\CacheFactory;
use ConsoleHelpers\CodeInsight\KnowledgeBase\DatabaseManager;
use ConsoleHelpers\CodeInsight\KnowledgeBase\KnowledgeBaseFactory;
use ConsoleHelpers\ConsoleKit\Config\ConfigEditor;
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

		$this['config_defaults'] = array(
			'cache.provider' => '',
		);

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
			return new DatabaseManager($c['migration_manager'], $c['working_directory']);
		};

		$this['knowledge_base_factory'] = function ($c) {
			return new KnowledgeBaseFactory($c['db_manager']);
		};

		$this['bc_checker_factory'] = function ($c) {
			$cache = $c['cache'];

			$factory = new CheckerFactory();
			$factory->add(new ClassChecker($cache));
			$factory->add(new FunctionChecker($cache));
			$factory->add(new ConstantChecker($cache));

			$factory->add(new InPortalClassChecker($cache));

			return $factory;
		};

		$this['bc_reporter_factory'] = function ($c) {
			$factory = new ReporterFactory();
			$factory->add(new TextReporter());
			$factory->add(new HtmlReporter());
			$factory->add(new JsonReporter());

			return $factory;
		};

		$this['bc_break_filter'] = function ($c) {
			return new BreakFilter();
		};

		$this['cache'] = function ($c) {
			/** @var ConfigEditor $config_editor */
			$config_editor = $c['config_editor'];
			$cache_provider = $config_editor->get('cache.provider');

			$cache_factory = new CacheFactory('');

			return $cache_factory->create('chain', array('array', $cache_provider));
		};
	}

}
