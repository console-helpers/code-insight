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
use ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector\ConstantDataCollector;

class ConstantDataCollectorTest extends AbstractDataCollectorTestCase
{

	public function testDefinedConstantChanges()
	{
		$this->initFixture('DefinedConstantsBefore');
		$this->collectData();

		$this->assertTableContent(
			'Constants',
			array(
				array(
					'FileId' => '1',
					'Name' => 'CONSTANT_ONE',
					'Value' => '"value1"',
				),
				array(
					'FileId' => '1',
					'Name' => 'CONSTANT_TWO',
					'Value' => '"value2"',
				),
				array(
					'FileId' => '1',
					'Name' => 'CONSTANT_THREE',
					'Value' => '"value3"',
				),
			)
		);
		$this->assertTablesEmpty();

		$this->initFixture('DefinedConstantsAfter');
		$this->collectData();

		$this->assertTableContent(
			'Constants',
			array(
				array(
					'FileId' => '1',
					'Name' => 'CONSTANT_ONE',
					'Value' => '"value1-new"',
				),
				array(
					'FileId' => '1',
					'Name' => 'CONSTANT_THREE',
					'Value' => '"value3"',
				),
			)
		);
		$this->assertTablesEmpty();
	}

	public function testDeleteData()
	{
		$this->initFixture('DefinedConstantsBefore');
		$this->collectData();

		$this->assertTableCount('Constants', 3);

		$this->dataCollector->deleteData(array($this->fileId));

		$this->assertTablesEmpty();
	}

	public function testGetStatistics()
	{
		$this->initFixture('DefinedConstantsBefore');
		$this->collectData();

		$this->assertSame(
			array(
				'Constants' => '3',
			),
			$this->dataCollector->getStatistics()
		);
	}

	/**
	 * Initializes fixture.
	 *
	 * @param string $fixture Fixture.
	 *
	 * @return void
	 */
	protected function initFixture($fixture)
	{
		$this->fixturePath = $this->getFixturePath('ConstantDataCollector/' . $fixture . '.php');

		if ( !$this->fileId ) {
			$this->fileId = $this->createFileMention($this->fixturePath);
		}
	}

	/**
	 * Creates data collector.
	 *
	 * @return AbstractDataCollector
	 */
	protected function createDataCollector()
	{
		return new ConstantDataCollector($this->database);
	}

}
