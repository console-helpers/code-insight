<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\Command\Dev;


use ConsoleHelpers\CodeInsight\Command\AbstractCommand;
use ConsoleHelpers\DatabaseMigration\MigrationManager;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationCreateCommand extends AbstractCommand
{

	/**
	 * Migration manager.
	 *
	 * @var MigrationManager
	 */
	private $_migrationManager;

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('dev:migration-create')
			->setDescription(
				'Creates new database migration'
			)
			->addArgument(
				'name',
				InputArgument::REQUIRED,
				'Migration name'
			)
			->addOption(
				'extension',
				'e',
				InputOption::VALUE_REQUIRED,
				'Migration file extension',
				'sql'
			);

		parent::configure();
	}

	/**
	 * Prepare dependencies.
	 *
	 * @return void
	 */
	protected function prepareDependencies()
	{
		parent::prepareDependencies();

		$container = $this->getContainer();

		$this->_migrationManager = $container['migration_manager'];
	}

	/**
	 * Return possible values for the named option
	 *
	 * @param string            $optionName Option name.
	 * @param CompletionContext $context    Completion context.
	 *
	 * @return array
	 */
	public function completeOptionValues($optionName, CompletionContext $context)
	{
		$ret = parent::completeOptionValues($optionName, $context);

		if ( $optionName === 'extension' ) {
			return $this->_migrationManager->getMigrationFileExtensions();
		}

		return $ret;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$migration_name = $this->_migrationManager->createMigration(
			$this->io->getArgument('name'),
			$this->io->getOption('extension')
		);

		$this->io->writeln('Migration <info>' . $migration_name . '</info> created.');
	}

}
