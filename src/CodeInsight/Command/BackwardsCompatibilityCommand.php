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
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\BreakFilter;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\AbstractChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker\CheckerFactory;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\Reporter\ReporterFactory;
use ConsoleHelpers\CodeInsight\KnowledgeBase\KnowledgeBaseFactory;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;
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
	 * Backwards compatibility reporter factory.
	 *
	 * @var ReporterFactory
	 */
	private $_reporterFactory;

	/**
	 * Backwards compatibility break filter.
	 *
	 * @var BreakFilter
	 */
	private $_breakFilter;

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
				InputArgument::OPTIONAL,
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
			)
			->addOption(
				'format',
				null,
				InputOption::VALUE_REQUIRED,
				'Output format, e.g. <comment>text</comment>, <comment>html</comment>, <comment>json</comment>',
				'text'
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
		$this->_reporterFactory = $container['bc_reporter_factory'];
		$this->_breakFilter = $container['bc_break_filter'];
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

		if ( $optionName === 'source-project-fork' ) {
			$input = $this->getInputFromCompletionContext($context);

			return $this->_knowledgeBaseFactory->getForks($this->getSourcePath($input, true));
		}

		if ( $optionName === 'target-project-fork' ) {
			$input = $this->getInputFromCompletionContext($context);

			return $this->_knowledgeBaseFactory->getForks($this->getTargetPath($input));
		}

		if ( $optionName === 'format' ) {
			return $this->_reporterFactory->getNames();
		}

		return $ret;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Get reporter upfront so that we can error out early for invalid reporters.
		$reporter = $this->_reporterFactory->get($this->io->getOption('format'));

		$source_path = $this->getSourcePath($input, false);
		$target_path = $this->getTargetPath($input);

		$source_knowledge_base = $this->_knowledgeBaseFactory->getKnowledgeBase(
			$source_path,
			$this->io->getOption('source-project-fork'),
			$this->io
		);
		$target_knowledge_base = $this->_knowledgeBaseFactory->getKnowledgeBase(
			$target_path,
			$this->io->getOption('target-project-fork'),
			$this->io
		);

		$bc_breaks = $this->getBackwardsCompatibilityBreaks(
			$source_knowledge_base->getDatabase(),
			$target_knowledge_base->getDatabase(),
			$target_knowledge_base->getBackwardsCompatibilityCheckers($this->_checkerFactory)
		);
		$bc_breaks = $this->_breakFilter->removeMatching(
			$bc_breaks,
			$target_knowledge_base->getBackwardsCompatibilityIgnoreRules()
		);

		$this->io->writeln($reporter->generate($bc_breaks));
	}

	/**
	 * Returns source path.
	 *
	 * @param InputInterface $input        Input.
	 * @param boolean        $autocomplete Autocomplete.
	 *
	 * @return string
	 * @throws RuntimeException When source project path is missing.
	 */
	protected function getSourcePath(InputInterface $input, $autocomplete)
	{
		$source_path = $this->getPath($input->getArgument('source-project-path'));

		if ( $source_path ) {
			return $source_path;
		}

		// Single code base, but comparing with fork OR autocompleting forks.
		if ( $autocomplete || $input->getOption('source-project-fork') ) {
			return $this->getTargetPath($input);
		}

		// Not using fork, then need to specify project path.
		throw new RuntimeException('Not enough arguments (missing: "source-project-path").');
	}

	/**
	 * Returns target path.
	 *
	 * @param InputInterface $input Input.
	 *
	 * @return string
	 */
	protected function getTargetPath(InputInterface $input)
	{
		return $this->getPath($input->getArgument('target-project-path'));
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

}
