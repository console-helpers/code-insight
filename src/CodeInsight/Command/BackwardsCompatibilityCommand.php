<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\Command;


use Aura\Sql\ExtendedPdoInterface;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\AbstractChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\CheckerFactory;
use ConsoleHelpers\CodeInsight\KnowledgeBase\KnowledgeBaseFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BackwardsCompatibilityCommand extends AbstractCommand
{

	/**
	 * Knowledge base factory.
	 *
	 * @var KnowledgeBaseFactory
	 */
	private $_knowledgeBaseFactory;

	/**
	 * Backwards compatibility checker factory.
	 *
	 * @var CheckerFactory
	 */
	private $_checkerFactory;

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('bc')
			->setDescription('Detects backwards compatibility breaks between 2 project versions')
			->addArgument(
				'source-project-path',
				InputArgument::REQUIRED,
				'Path to source project root folder (where <comment>.code-insight.json</comment> is located)'
			)
			->addArgument(
				'target-project-path',
				InputArgument::OPTIONAL,
				'Path to target project root folder (where <comment>.code-insight.json</comment> is located)',
				'.'
			)
			->addOption(
				'source-project-fork',
				null,
				InputOption::VALUE_REQUIRED,
				'Source project fork name'
			)
			->addOption(
				'target-project-fork',
				null,
				InputOption::VALUE_REQUIRED,
				'Target project fork name'
			);
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

		$this->_knowledgeBaseFactory = $container['knowledge_base_factory'];
		$this->_checkerFactory = $container['bc_checker_factory'];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$source_knowledge_base = $this->_knowledgeBaseFactory->getKnowledgeBase(
			$this->getPath('source-project-path'),
			$this->io->getOption('source-project-fork'),
			$this->io
		);
		$target_knowledge_base = $this->_knowledgeBaseFactory->getKnowledgeBase(
			$this->getPath('target-project-path'),
			$this->io->getOption('target-project-fork'),
			$this->io
		);

		$bc_breaks = $this->getBackwardsCompatibilityBreaks(
			$source_knowledge_base->getDatabase(),
			$target_knowledge_base->getDatabase(),
			$target_knowledge_base->getBackwardsCompatibilityCheckers($this->_checkerFactory)
		);

		if ( !$bc_breaks ) {
			$this->io->writeln('No backwards compatibility breaks detected.');

			return;
		}

		$this->io->writeln('Backward compatibility breaks:');

		foreach ( $this->groupByType($bc_breaks) as $bc_break => $incidents ) {
			$bc_break = ucwords(str_replace(array('.', '_'), ' ', $bc_break));
			$this->io->writeln('<fg=red>=== ' . $bc_break . ' (' . count($incidents) . ') ===</>');

			foreach ( $incidents as $incident_data ) {
				if ( array_key_exists('old', $incident_data) ) {
					$this->io->writeln(' * <fg=white;options=bold>' . $incident_data['element'] . '</>');
					$this->io->writeln('   OLD: ' . $incident_data['old']);
					$this->io->writeln('   NEW: ' . $incident_data['new']);
				}
				else {
					$this->io->writeln(' * ' . $incident_data['element']);
				}
			}

			$this->io->writeln('');
		}
	}

	/**
	 * Finds backward compatibility breaks.
	 *
	 * @param ExtendedPdoInterface $source_db Source database.
	 * @param ExtendedPdoInterface $target_db Target database.
	 * @param AbstractChecker[]    $checkers  Checkers.
	 *
	 * @return array
	 */
	protected function getBackwardsCompatibilityBreaks(
		ExtendedPdoInterface $source_db,
		ExtendedPdoInterface $target_db,
		array $checkers
	) {
		$breaks = array();

		foreach ( $checkers as $checker ) {
			$breaks = array_merge($breaks, $checker->check($source_db, $target_db));
		}

		return $breaks;
	}

	/**
	 * Groups BC breaks by type.
	 *
	 * @param array $bc_breaks BC breaks.
	 *
	 * @return array
	 */
	protected function groupByType(array $bc_breaks)
	{
		$ret = array();

		foreach ( $bc_breaks as $bc_break_data ) {
			$type = $bc_break_data['type'];

			if ( !isset($ret[$type]) ) {
				$ret[$type] = array();
			}

			$ret[$type][] = $bc_break_data;
		}

		return $ret;
	}

}
