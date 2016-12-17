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

	protected function setUp()
	{
		parent::setUp();

		$this->dataCollector = $this->createDataCollector();
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
	 * @param        $file_id
	 * @param string $filename  Filename.
	 * @param string $namespace Namespace.
	 */
	protected function collectData($file_id, $filename, $namespace = '')
	{
		$file = new ReflectionFile($filename);
		$this->dataCollector->collectData($file_id, $file->getFileNamespace($namespace));
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
