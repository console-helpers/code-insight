<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace Tests\ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector;


use ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector\AbstractDataCollector;
use Go\ParserReflection\ReflectionFile;
use Tests\ConsoleHelpers\CodeInsight\AbstractDatabaseAwareTestCase;

abstract class AbstractDataCollectorTestCase extends AbstractDatabaseAwareTestCase
{

	/**
	 * Data collector.
	 *
	 * @var AbstractDataCollector
	 */
	protected $dataCollector;

	/**
	 * Fixture path.
	 *
	 * @var string
	 */
	protected $fixturePath;

	/**
	 * File ID.
	 *
	 * @var integer
	 */
	protected $fileId;

	/**
	 * Mapping for locating classes by fixture locator.
	 *
	 * @var array
	 */
	protected $fixtureLocatorMapping = array();

	/**
	 * All tables in a database.
	 *
	 * @var array
	 */
	private $_allTables = array(
		'Files',
		'Functions', 'FunctionParameters', 'Constants',
		'Classes', 'ClassRelations', 'ClassConstants', 'ClassProperties', 'ClassMethods', 'MethodParameters',
	);

	/**
	 * Tables, that are supposed to be non-empty.
	 *
	 * @var array
	 */
	private $_nonEmptyTables = array();

	protected function setUp()
	{
		parent::setUp();

		$this->dataCollector = $this->createDataCollector();
	}

	/**
	 * Checks, table content.
	 *
	 * @param string $table_name       Table name.
	 * @param array  $expected_content Expected content.
	 *
	 * @return void
	 */
	protected function assertTableContent($table_name, array $expected_content)
	{
		$this->_nonEmptyTables[] = $table_name;

		parent::assertTableContent($table_name, $expected_content);
	}

	/**
	 * Checks, that database table is empty.
	 *
	 * @param array $table_names Table names.
	 *
	 * @return void
	 */
	protected function assertTablesEmpty(array $table_names = array())
	{
		if ( !$table_names ) {
			$table_names = array_diff($this->_allTables, $this->_nonEmptyTables);
		}

		parent::assertTablesEmpty($table_names);
	}

	/**
	 * Creates mention about a file.
	 *
	 * @param string  $filename File name.
	 * @param integer $size     File size.
	 *
	 * @return int
	 */
	protected function createFileMention($filename, $size = 0)
	{
		$this->_nonEmptyTables[] = 'Files';

		$sql = 'INSERT INTO Files (Name, Size) VALUES (:name, :size)';
		$this->database->perform($sql, array(
			'name' => $filename,
			'size' => $size,
		));

		return $this->database->lastInsertId();
	}

	/**
	 * Collects data from a given fixture's namespace.
	 *
	 * @param string $namespace Namespace.
	 *
	 * @return void
	 */
	protected function collectData($namespace = '')
	{
		$file = new ReflectionFile($this->fixturePath);
		$this->dataCollector->collectData($this->fileId, $file->getFileNamespace($namespace));
	}

	/**
	 * Returns absolute path to fixture file.
	 *
	 * @param string $fixture Fixture.
	 *
	 * @return string
	 */
	protected function getFixturePath($fixture)
	{
		return __DIR__ . '/fixtures/' . $fixture;
	}

	/**
	 * Creates data collector.
	 *
	 * @return AbstractDataCollector
	 */
	abstract protected function createDataCollector();

}
