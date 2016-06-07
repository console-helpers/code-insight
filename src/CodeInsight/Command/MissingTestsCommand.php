<?php
namespace ConsoleHelpers\CodeInsight\Command;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MissingTestsCommand extends AbstractCommand
{

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('missing-tests')
			->setDescription('Shows classes without tests')
			->addArgument(
				'src-path',
				InputArgument::OPTIONAL,
				'Path to folder with source code',
				'src'
			)
			->addArgument(
				'tests-path',
				InputArgument::OPTIONAL,
				'Path to folder with tests',
				'tests'
			);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$src_path = $this->getPath('src-path');
		$tests_path = $this->getPath('tests-path');

		$finder = new Finder();
		$finder->files()->name('*.php')->notName('/^(I[A-Z]|Abstract[A-Z])/')->in($src_path);

		$missing_tests = array();
		$src_path_parent_path = dirname($src_path) . '/';

		foreach ( $finder as $source_file ) {
			$source_file_path = $source_file->getPathname();
			$test_file_path = preg_replace(
				'/^' . preg_quote($src_path, '/') . '(.*)\.php$/',
				$tests_path . '$1Test.php',
				$source_file_path
			);

			if ( !file_exists($test_file_path) ) {
				$missing_tests[] = str_replace($src_path_parent_path, '', $source_file_path);
			}
		}

		if ( !$missing_tests ) {
			$this->io->writeln('<info>Tests for all source files are found.</info>');
		}
		else {
			$this->io->writeln('<error>Tests for following source files are missing:</error>');

			foreach ( $missing_tests as $missing_test ) {
				$this->io->writeln(' * ' . $missing_test);
			}
		}
	}

	/**
	 * Returns and validates path.
	 *
	 * @param string $argument_name Argument name, that contains path.
	 *
	 * @return string
	 * @throws \InvalidArgumentException When path isn't valid.
	 */
	protected function getPath($argument_name)
	{
		$raw_path = $this->io->getArgument($argument_name);
		$path = realpath($raw_path);

		if ( !$path || !file_exists($path) || !is_dir($path) ) {
			throw new \InvalidArgumentException('The "' . $raw_path . '" path is invalid.');
		}

		return $path;
	}

}
