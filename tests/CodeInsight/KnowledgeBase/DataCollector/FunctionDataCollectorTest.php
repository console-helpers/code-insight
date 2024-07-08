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
use ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector\FunctionDataCollector;

class FunctionDataCollectorTest extends AbstractDataCollectorTestCase
{

	public function testFunctionFlagChanges()
	{
		$this->initFixture('FunctionFlagsBefore');
		$this->collectData();

		$this->assertTableContent(
			'Functions',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'functionOne',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '2',
					'FileId' => '1',
					'Name' => 'functionTwo',
					'ParameterCount' => '1',
					'RequiredParameterCount' => '0',
					'IsVariadic' => '1',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '3',
					'FileId' => '1',
					'Name' => 'functionThree',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '1',
					'ReturnType' => 'string',
				),
				array(
					'Id' => '4',
					'FileId' => '1',
					'Name' => 'functionFour',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '1',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
			)
		);
		$this->assertTableContent(
			'FunctionParameters',
			array(
				array(
					'FunctionId' => '2',
					'Name' => 'numbers',
					'Position' => '0',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '1',
					'IsVariadic' => '1',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
			)
		);
		$this->assertTablesEmpty();

		$this->initFixture('FunctionFlagsAfter');
		$this->collectData();

		$this->assertTableContent(
			'Functions',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'functionOne',
					'ParameterCount' => '1',
					'RequiredParameterCount' => '0',
					'IsVariadic' => '1',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '2',
					'FileId' => '1',
					'Name' => 'functionTwo',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '3',
					'FileId' => '1',
					'Name' => 'functionThree',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '1',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '4',
					'FileId' => '1',
					'Name' => 'functionFour',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '1',
					'ReturnType' => 'string',
				),
			)
		);
		$this->assertTableContent(
			'FunctionParameters',
			array(
				array(
					'FunctionId' => '1',
					'Name' => 'numbers',
					'Position' => '0',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '1',
					'IsVariadic' => '1',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
			)
		);
		$this->assertTablesEmpty();
	}

	public function testFunctionParameterChanges()
	{
		$this->initFixture('FunctionParametersBefore');
		$this->collectData();

		$this->assertTableContent(
			'Functions',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'greedyFunction',
					'ParameterCount' => '8',
					'RequiredParameterCount' => '5',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '2',
					'FileId' => '1',
					'Name' => 'variadicFunction',
					'ParameterCount' => '2',
					'RequiredParameterCount' => '1',
					'IsVariadic' => '1',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
			)
		);
		$this->assertTableContent(
			'FunctionParameters',
			array(
				array(
					'FunctionId' => '1',
					'Name' => 'param_one',
					'Position' => '0',
					'TypeClass' => null,
					'HasType' => '1',
					'TypeName' => 'array',
					'AllowsNull' => '0',
					'IsArray' => '1',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_two',
					'Position' => '1',
					'TypeClass' => 'stdClass',
					'HasType' => '1',
					'TypeName' => '\\stdClass',
					'AllowsNull' => '0',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_three',
					'Position' => '2',
					'TypeClass' => null,
					'HasType' => '1',
					'TypeName' => 'string',
					'AllowsNull' => '0',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_four',
					'Position' => '3',
					'TypeClass' => null,
					'HasType' => '1',
					'TypeName' => 'callable',
					'AllowsNull' => '0',
					'IsArray' => '0',
					'IsCallable' => '1',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_five',
					'Position' => '4',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '0',
					'IsPassedByReference' => '1',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_six',
					'Position' => '5',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '1',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '1',
					'DefaultValue' => '"def"',
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_seven',
					'Position' => '6',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '1',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '1',
					'DefaultValue' => '"\n"',
					'DefaultConstant' => 'PHP_EOL',
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_eight',
					'Position' => '7',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '1',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '1',
					'DefaultValue' => 'null',
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '2',
					'Name' => 'param_nine',
					'Position' => '0',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '2',
					'Name' => 'param_ten',
					'Position' => '1',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '1',
					'IsVariadic' => '1',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
			)
		);
		$this->assertTablesEmpty();

		$this->initFixture('FunctionParametersAfter');
		$this->collectData();

		$this->assertTableContent(
			'Functions',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'greedyFunction',
					'ParameterCount' => '8',
					'RequiredParameterCount' => '6',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '2',
					'FileId' => '1',
					'Name' => 'variadicFunction',
					'ParameterCount' => '2',
					'RequiredParameterCount' => '1',
					'IsVariadic' => '1',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
			)
		);
		$this->assertTableContent(
			'FunctionParameters',
			array(
				array(
					'FunctionId' => '1',
					'Name' => 'param_one',
					'Position' => '0',
					'TypeClass' => 'stdClass',
					'HasType' => '1',
					'TypeName' => '\\stdClass',
					'AllowsNull' => '0',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_two',
					'Position' => '1',
					'TypeClass' => null,
					'HasType' => '1',
					'TypeName' => 'array',
					'AllowsNull' => '0',
					'IsArray' => '1',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_three',
					'Position' => '2',
					'TypeClass' => null,
					'HasType' => '1',
					'TypeName' => 'callable',
					'AllowsNull' => '0',
					'IsArray' => '0',
					'IsCallable' => '1',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_four',
					'Position' => '3',
					'TypeClass' => null,
					'HasType' => '1',
					'TypeName' => 'string',
					'AllowsNull' => '0',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_five',
					'Position' => '4',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '1',
					'DefaultValue' => '"def"',
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_six',
					'Position' => '5',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '0',
					'IsPassedByReference' => '1',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_seven',
					'Position' => '6',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '1',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '1',
					'DefaultValue' => 'null',
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '1',
					'Name' => 'param_eight',
					'Position' => '7',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '1',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '1',
					'DefaultValue' => '"\n"',
					'DefaultConstant' => 'PHP_EOL',
				),
				array(
					'FunctionId' => '2',
					'Name' => 'param_nine',
					'Position' => '1',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '1',
					'IsVariadic' => '1',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'FunctionId' => '2',
					'Name' => 'param_ten',
					'Position' => '0',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '0',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
			)
		);
		$this->assertTablesEmpty();
	}

	public function testDeleteData()
	{
		$this->initFixture('FunctionParametersBefore');
		$this->collectData();

		$this->assertTableCount('Functions', 2);
		$this->assertTableCount('FunctionParameters', 10);

		$this->dataCollector->deleteData(array($this->fileId));

		$this->assertTablesEmpty();
	}

	public function testGetStatistics()
	{
		$this->initFixture('FunctionParametersBefore');
		$this->collectData();

		$this->assertEquals(
			array(
				'Functions' => '2',
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
		$this->fixturePath = $this->getFixturePath('FunctionDataCollector/' . $fixture . '.php');

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
		return new FunctionDataCollector($this->database);
	}

}
