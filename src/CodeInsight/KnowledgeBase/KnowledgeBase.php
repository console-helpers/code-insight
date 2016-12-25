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


use Aura\Sql\ExtendedPdoInterface;
use Composer\Autoload\ClassLoader;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\AbstractChecker;
use ConsoleHelpers\CodeInsight\BackwardsCompatibility\CheckerFactory;
use ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector\AbstractDataCollector;
use ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector\ClassDataCollector;
use ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector\ConstantDataCollector;
use ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector\FunctionDataCollector;
use ConsoleHelpers\ConsoleKit\ConsoleIO;
use Go\ParserReflection\Locator\CallableLocator;
use Go\ParserReflection\Locator\ComposerLocator;
use Go\ParserReflection\LocatorInterface;
use Go\ParserReflection\ReflectionEngine;
use Go\ParserReflection\ReflectionFile;
use Symfony\Component\Finder\Finder;

class KnowledgeBase
{

	/**
	 * Project path.
	 *
	 * @var string
	 */
	protected $projectPath = '';

	/**
	 * Regular expression for removing project path.
	 *
	 * @var string
	 */
	protected $projectPathRegExp = '';

	/**
	 * Database.
	 *
	 * @var ExtendedPdoInterface
	 */
	protected $db;

	/**
	 * Config
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * Data collectors.
	 *
	 * @var AbstractDataCollector[]
	 */
	protected $dataCollectors = array();

	/**
	 * Console IO.
	 *
	 * @var ConsoleIO
	 */
	protected $io;

	/**
	 * Creates knowledge base instance.
	 *
	 * @param string               $project_path Project path.
	 * @param ExtendedPdoInterface $db           Database.
	 * @param ConsoleIO            $io           Console IO.
	 *
	 * @throws \InvalidArgumentException When project path doesn't exist.
	 */
	public function __construct($project_path, ExtendedPdoInterface $db, ConsoleIO $io = null)
	{
		if ( !file_exists($project_path) || !is_dir($project_path) ) {
			throw new \InvalidArgumentException('The project path doesn\'t exist.');
		}

		$this->projectPath = $project_path;
		$this->projectPathRegExp = '#^' . preg_quote($project_path, '#') . '/#';

		$this->db = $db;
		$this->config = $this->getConfiguration();
		$this->io = $io;

		$this->dataCollectors[] = new ClassDataCollector($db);
		$this->dataCollectors[] = new ConstantDataCollector($db);
		$this->dataCollectors[] = new FunctionDataCollector($db);
	}

	/**
	 * Returns database.
	 *
	 * @return ExtendedPdoInterface
	 */
	public function getDatabase()
	{
		return $this->db;
	}

	/**
	 * Returns project configuration.
	 *
	 * @return array
	 * @throws \LogicException When configuration file is not found.
	 * @throws \LogicException When configuration file isn't in JSON format.
	 */
	protected function getConfiguration()
	{
		$config_file = $this->projectPath . '/.code-insight.json';

		if ( !file_exists($config_file) ) {
			throw new \LogicException(
				'Configuration file ".code-insight.json" not found at "' . $this->projectPath . '".'
			);
		}

		$config = json_decode(file_get_contents($config_file), true);

		if ( $config === null ) {
			throw new \LogicException('Configuration file ".code-insight.json" is not in JSON format.');
		}

		return $config;
	}

	/**
	 * Refreshes database.
	 *
	 * @return void
	 * @throws \LogicException When "$this->io" wasn't set upfront.
	 */
	public function refresh()
	{
		if ( !isset($this->io) ) {
			throw new \LogicException('The "$this->io" must be set prior to calling "$this->refresh()".');
		}

		ReflectionEngine::setMaximumCachedFiles(20);
		ReflectionEngine::init($this->detectClassLocator());

		$sql = 'UPDATE Files
				SET Found = 0';
		$this->db->perform($sql);

		$files = array();
		$this->io->write('Searching for files ... ');

		foreach ( $this->getFinders() as $finder ) {
			$files = array_merge($files, array_keys(iterator_to_array($finder)));
		}

		$file_count = count($files);
		$this->io->writeln(array('<info>' . $file_count . ' found</info>', ''));

		$progress_bar = $this->io->createProgressBar($file_count + 2);
		$progress_bar->setMessage('');
		$progress_bar->setFormat(
			'%message%' . PHP_EOL . '%current%/%max% [%bar%] <info>%percent:3s%%</info> %elapsed:6s%/%estimated:-6s% <info>%memory:-10s%</info>'
		);
		$progress_bar->start();

		foreach ( $files as $file ) {
			$progress_bar->setMessage('Processing file: <info>' . $this->removeProjectPath($file) . '</info>');
			$progress_bar->display();

			$this->processFile($file);

			$progress_bar->advance();
		}

		$sql = 'SELECT Id
				FROM Files
				WHERE Found = 0';
		$deleted_files = $this->db->fetchCol($sql);

		if ( $deleted_files ) {
			$progress_bar->setMessage('Erasing information about deleted files ...');
			$progress_bar->display();

			foreach ( $this->dataCollectors as $data_collector ) {
				$data_collector->deleteData($deleted_files);
			}

			$progress_bar->advance();
		}

		$progress_bar->setMessage('Aggregating processed data ...');
		$progress_bar->display();

		foreach ( $this->dataCollectors as $data_collector ) {
			$data_collector->aggregateData($this);
		}

		$progress_bar->advance();

		$progress_bar->finish();
		$progress_bar->clear();
	}

	/**
	 * Prints statistics about the code.
	 *
	 * @return array
	 */
	public function getStatistics()
	{
		$ret = array();

		$sql = 'SELECT COUNT(*)
				FROM Files';
		$ret['Files'] = $this->db->fetchValue($sql);

		foreach ( $this->dataCollectors as $data_collector ) {
			$ret = array_merge($ret, $data_collector->getStatistics());
		}

		return $ret;
	}

	/**
	 * Processes file.
	 *
	 * @param string $file File.
	 *
	 * @return integer
	 */
	public function processFile($file)
	{
		$size = filesize($file);
		$relative_file = $this->removeProjectPath($file);

		$sql = 'SELECT Id, Size
				FROM Files
				WHERE Name = :name';
		$file_data = $this->db->fetchOne($sql, array(
			'name' => $relative_file,
		));

		$this->db->beginTransaction();

		if ( $file_data === false ) {
			$sql = 'INSERT INTO Files (Name, Size) VALUES (:name, :size)';
			$this->db->perform($sql, array(
				'name' => $relative_file,
				'size' => $size,
			));

			$file_id = $this->db->lastInsertId();
		}
		else {
			$file_id = $file_data['Id'];
		}

		// File is not changed since last time it was indexed.
		if ( $file_data !== false && (int)$file_data['Size'] === $size ) {
			$sql = 'UPDATE Files
					SET Found = 1
					WHERE Id = :file_id';
			$this->db->perform($sql, array(
				'file_id' => $file_data['Id'],
			));

			$this->db->commit();

			return $file_data['Id'];
		}

		$sql = 'UPDATE Files
				SET Found = 1
				WHERE Id = :file_id';
		$this->db->perform($sql, array(
			'file_id' => $file_data['Id'],
		));

		$parsed_file = new ReflectionFile($file);

		foreach ( $parsed_file->getFileNamespaces() as $namespace ) {
			foreach ( $this->dataCollectors as $data_collector ) {
				$data_collector->collectData($file_id, $namespace);
			}
		}

		$this->db->commit();

		return $file_id;
	}

	/**
	 * Determines class locator.
	 *
	 * @return LocatorInterface
	 * @throws \LogicException When class locator from "class_locator" setting doesn't exist.
	 * @throws \LogicException When class locator from "class_locator" setting has non supported type.
	 */
	protected function detectClassLocator()
	{
		$class_locator = null;
		$raw_class_locator_file = $this->getConfigSetting('class_locator');

		if ( $raw_class_locator_file !== null ) {
			$class_locator_file = $this->resolveProjectPath($raw_class_locator_file);

			if ( !file_exists($class_locator_file) || !is_file($class_locator_file) ) {
				throw new \LogicException(
					'The "' . $raw_class_locator_file . '" class locator doesn\'t exist.'
				);
			}

			$class_locator = require $class_locator_file;
		}
		else {
			$class_locator_file = $this->resolveProjectPath('vendor/autoload.php');

			if ( file_exists($class_locator_file) && is_file($class_locator_file) ) {
				$class_locator = require $class_locator_file;
			}
		}

		// Make sure memory limit isn't changed by class locator.
		ini_restore('memory_limit');

		if ( is_callable($class_locator) ) {
			return new CallableLocator($class_locator);
		}
		elseif ( $class_locator instanceof ClassLoader ) {
			return new ComposerLocator($class_locator);
		}

		throw new \LogicException(
			'The "class_loader" setting must point to "vendor/autoload.php" or a file, that would return the closure.'
		);
	}

	/**
	 * Processes the Finders configuration list.
	 *
	 * @return Finder[]
	 * @throws \LogicException If "finder" setting doesn't exist.
	 * @throws \LogicException If the configured method does not exist.
	 */
	protected function getFinders()
	{
		$finder_config = $this->getConfigSetting('finder');

		// Process "finder" config setting.
		if ( $finder_config === null ) {
			throw new \LogicException('The "finder" setting must be present in config file.');
		}

		$finders = array();

		foreach ( $finder_config as $methods ) {
			$finder = Finder::create()->files();

			if ( isset($methods['in']) ) {
				$methods['in'] = (array)$methods['in'];

				foreach ( $methods['in'] as $folder_index => $in_folder ) {
					$methods['in'][$folder_index] = $this->resolveProjectPath($in_folder);
				}
			}

			foreach ( $methods as $method => $arguments ) {
				if ( !method_exists($finder, $method) ) {
					throw new \LogicException(sprintf(
						'The method "Finder::%s" does not exist.',
						$method
					));
				}

				$arguments = (array)$arguments;

				foreach ( $arguments as $argument ) {
					$finder->$method($argument);
				}
			}

			$finders[] = $finder;
		}

		return $finders;
	}

	/**
	 * Resolves path within project.
	 *
	 * @param string $relative_path Relative path.
	 *
	 * @return string
	 */
	protected function resolveProjectPath($relative_path)
	{
		return realpath($this->projectPath . DIRECTORY_SEPARATOR . $relative_path);
	}

	/**
	 * Removes project path from file path.
	 *
	 * @param string $absolute_path Absolute path.
	 *
	 * @return string
	 */
	protected function removeProjectPath($absolute_path)
	{
		return preg_replace($this->projectPathRegExp, '', $absolute_path, 1);
	}

	/**
	 * Returns backwards compatibility checkers.
	 *
	 * @param CheckerFactory $factory Factory.
	 *
	 * @return AbstractChecker[]
	 */
	public function getBackwardsCompatibilityCheckers(CheckerFactory $factory)
	{
		$ret = array();
		$default_names = array('class', 'function', 'constant');

		foreach ( $this->getConfigSetting('bc_checkers', $default_names) as $name ) {
			$ret[] = $factory->get($name);
		}

		return $ret;
	}

	/**
	 * Returns value of configuration setting.
	 *
	 * @param string     $name    Name.
	 * @param mixed|null $default Default value.
	 *
	 * @return mixed
	 */
	protected function getConfigSetting($name, $default = null)
	{
		return array_key_exists($name, $this->config) ? $this->config[$name] : $default;
	}

}
