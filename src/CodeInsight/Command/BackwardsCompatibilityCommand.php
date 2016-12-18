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


use ConsoleHelpers\CodeInsight\KnowledgeBase\KnowledgeBaseFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('backwards-compatibility')
			->setAliases(array('bc'))
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
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$source_knowledge_base = $this->_knowledgeBaseFactory->getKnowledgeBase(
			$this->getPath('source-project-path'),
			$this->io
		);
		$target_knowledge_base = $this->_knowledgeBaseFactory->getKnowledgeBase(
			$this->getPath('target-project-path'),
			$this->io
		);

		$bc_breaks = $target_knowledge_base->getBackwardsCompatibilityBreaks($source_knowledge_base->getDatabase());

		if ( !$bc_breaks ) {
			$this->io->writeln('No backwards compatibility breaks detected.');

			return;
		}

		$this->io->writeln('Backward compatibility breaks:');

		foreach ( $bc_breaks as $bc_break => $incidents ) {
			$this->io->writeln('<fg=red>=== ' . $bc_break . ' (' . count($incidents) . ') ===</>');

			foreach ( $incidents as $incident ) {
				$incident = implode(PHP_EOL . '   ', explode(PHP_EOL, $incident));

				$this->io->writeln(' * ' . $incident);
			}

			$this->io->writeln('');
		}
	}

}
