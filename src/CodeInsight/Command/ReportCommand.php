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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReportCommand extends AbstractCommand
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
			->setName('report')
			->setDescription('Analyzes project and shows the report')
			->addArgument(
				'project-path',
				InputArgument::OPTIONAL,
				'Path to project root folder (where <comment>.code-insight.json</comment> is located)',
				'.'
			)
			->addOption(
				'refresh',
				'r',
				InputOption::VALUE_NONE,
				'Refreshes database'
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
		$knowledge_base = $this->_knowledgeBaseFactory->getKnowledgeBase(
			$this->getPath('project-path'),
			$this->io
		);

		if ( $this->io->getOption('refresh') ) {
			$knowledge_base->refresh();
			$this->io->writeln('');
		}

		$this->io->writeln('<comment>Results:</comment>');

		foreach ( $knowledge_base->getStatistics() as $name => $value ) {
			$this->io->writeln(' * ' . $name . ': <info>' . $value . '</info>');
		}
	}

}
