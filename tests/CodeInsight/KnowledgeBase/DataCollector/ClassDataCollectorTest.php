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
use ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector\ClassDataCollector;
use ConsoleHelpers\CodeInsight\KnowledgeBase\KnowledgeBase;
use Go\ParserReflection\Locator\CallableLocator;
use Go\ParserReflection\ReflectionEngine;
use Tests\ConsoleHelpers\CodeInsight\ProphecyToken\RegExToken;

class ClassDataCollectorTest extends AbstractDataCollectorTestCase
{

	/**
	 * Mapping between class names and files, where they're located.
	 *
	 * @var array
	 */
	protected $classMapping = array(
		'AbstractClass' => 'ClassFlags.php',
		'FinalClass' => 'ClassFlags.php',
		'UserClass' => 'ClassRelations.php',
		'UserInterface' => 'ClassRelations.php',
		'UserTrait' => 'ClassRelations.php',
		'TheClass' => 'ClassTypes.php',
		'TheInterface' => 'ClassTypes.php',
		'TheTrait' => 'ClassTypes.php',
		'ClassRelationChangesA' => 'ClassRelationsBefore.php',
		'ClassRelationChangesB' => 'ClassRelationsBefore.php',
		'ClassRelationChangesC' => 'ClassRelationsBefore.php',
		'ClassRelationChangesD' => 'ClassRelationsBefore.php',
		'DynamicDataRemovalTwo' => 'DynamicDataRemoval.php',
	);

	protected function setUp()
	{
		parent::setUp();

		ReflectionEngine::init(new CallableLocator(array($this, 'locateFixtureClass')));
	}

	public function testNoClasses()
	{
		$this->initFixture('NoClasses');
		$this->collectData();

		$this->assertTablesEmpty();
	}

	public function testNamespacedClass()
	{
		$this->initFixture('NamespacedClass');

		// Repeat twice to ensure existing DB record was reused.
		for ( $i = 0; $i < 2; $i++ ) {
			$this->collectData('LevelOne\LevelTwo');

			$this->assertTableContent(
				'Classes',
				array(
					array(
						'Id' => '1',
						'FileId' => '1',
						'Name' => 'LevelOne\\LevelTwo\\NamespacedClass',
						'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
						'IsAbstract' => '0',
						'IsFinal' => '0',
						'RawRelations' => $i === 0 ? null : '[]',
					),
				)
			);
			$this->assertTablesEmpty();
		}
	}

	public function testGlobalClass()
	{
		$this->initFixture('GlobalClass');

		// Repeat twice to ensure existing DB record was reused.
		for ( $i = 0; $i < 2; $i++ ) {
			$this->collectData();

			$this->assertTableContent(
				'Classes',
				array(
					array(
						'Id' => '1',
						'FileId' => '1',
						'Name' => 'GlobalClass',
						'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
						'IsAbstract' => '0',
						'IsFinal' => '0',
						'RawRelations' => $i === 0 ? null : '[]',
					),
				)
			);
			$this->assertTablesEmpty();
		}
	}

	public function testClassFlagChanges()
	{
		$this->initFixture('ClassFlagsBefore');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassA',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
				array(
					'Id' => '2',
					'FileId' => '1',
					'Name' => 'ClassB',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '1',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
				array(
					'Id' => '3',
					'FileId' => '1',
					'Name' => 'ClassC',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '1',
					'RawRelations' => null,
				),
			)
		);
		$this->assertTablesEmpty();

		// Confirm, that existing DB records are updated.
		$this->initFixture('ClassFlagsAfter');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassA',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '1',
					'IsFinal' => '0',
					'RawRelations' => '[]',
				),
				array(
					'Id' => '2',
					'FileId' => '1',
					'Name' => 'ClassB',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '1',
					'RawRelations' => '[]',
				),
				array(
					'Id' => '3',
					'FileId' => '1',
					'Name' => 'ClassC',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => '[]',
				),
			)
		);
		$this->assertTablesEmpty();
	}

	public function testClassTypeChanges()
	{
		$this->initFixture('ClassTypesBefore');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassA',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
				array(
					'Id' => '2',
					'FileId' => '1',
					'Name' => 'ClassB',
					'ClassType' => (string)ClassDataCollector::TYPE_INTERFACE,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
				array(
					'Id' => '3',
					'FileId' => '1',
					'Name' => 'ClassC',
					'ClassType' => (string)ClassDataCollector::TYPE_TRAIT,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
			)
		);
		$this->assertTablesEmpty();

		// Confirm, that existing DB records are updated.
		$this->initFixture('ClassTypesAfter');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassA',
					'ClassType' => (string)ClassDataCollector::TYPE_INTERFACE,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => '[]',
				),
				array(
					'Id' => '2',
					'FileId' => '1',
					'Name' => 'ClassB',
					'ClassType' => (string)ClassDataCollector::TYPE_TRAIT,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => '[]',
				),
				array(
					'Id' => '3',
					'FileId' => '1',
					'Name' => 'ClassC',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => '[]',
				),
			)
		);
		$this->assertTablesEmpty();
	}

	public function testClassProperties()
	{
		$this->initFixture('ClassProperties');

		// Repeat twice to ensure existing DB record was reused.
		for ( $i = 0; $i < 2; $i++ ) {
			$this->collectData();

			$this->assertTableContent(
				'Classes',
				array(
					array(
						'Id' => '1',
						'FileId' => '1',
						'Name' => 'ClassProperties',
						'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
						'IsAbstract' => '0',
						'IsFinal' => '0',
						'RawRelations' => $i === 0 ? null : '[]',
					),
				)
			);
			$this->assertTableContent(
				'ClassProperties',
				array(
					array(
						'ClassId' => '1',
						'Name' => 'publicProperty',
						'Value' => 'null',
						'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
						'IsStatic' => '0',
					),
					array(
						'ClassId' => '1',
						'Name' => 'protectedProperty',
						'Value' => 'null',
						'Scope' => (string)ClassDataCollector::SCOPE_PROTECTED,
						'IsStatic' => '0',
					),
					array(
						'ClassId' => '1',
						'Name' => 'privateProperty',
						'Value' => 'null',
						'Scope' => (string)ClassDataCollector::SCOPE_PRIVATE,
						'IsStatic' => '0',
					),
					array(
						'ClassId' => '1',
						'Name' => 'propertyWithDefault',
						'Value' => '"non-empty"',
						'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
						'IsStatic' => '0',
					),
					array(
						'ClassId' => '1',
						'Name' => 'staticPropertyWithoutDefault',
						'Value' => 'null',
						'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
						'IsStatic' => '1',
					),
					array(
						'ClassId' => '1',
						'Name' => 'staticPropertyWithDefault',
						'Value' => '"non-empty"',
						'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
						'IsStatic' => '1',
					),
				)
			);
			$this->assertTablesEmpty();
		}
	}

	public function testClassPropertyChanges()
	{
		$this->initFixture('ClassPropertiesBefore');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassProperties',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
			)
		);
		$this->assertTableContent(
			'ClassProperties',
			array(
				array(
					'ClassId' => '1',
					'Name' => 'defaultValueChangeFromEmpty',
					'Value' => 'null',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '0',
				),
				array(
					'ClassId' => '1',
					'Name' => 'defaultValueChangeToEmpty',
					'Value' => '"non-empty"',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '0',
				),
				array(
					'ClassId' => '1',
					'Name' => 'scopeChange',
					'Value' => 'null',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '0',
				),
				array(
					'ClassId' => '1',
					'Name' => 'staticChange',
					'Value' => 'null',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '0',
				),
			)
		);
		$this->assertTablesEmpty();

		$this->initFixture('ClassPropertiesAfter');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassProperties',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => '[]',
				),
			)
		);
		$this->assertTableContent(
			'ClassProperties',
			array(
				array(
					'ClassId' => '1',
					'Name' => 'defaultValueChangeFromEmpty',
					'Value' => '"non-empty"',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '0',
				),
				array(
					'ClassId' => '1',
					'Name' => 'defaultValueChangeToEmpty',
					'Value' => 'null',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '0',
				),
				array(
					'ClassId' => '1',
					'Name' => 'scopeChange',
					'Value' => 'null',
					'Scope' => (string)ClassDataCollector::SCOPE_PROTECTED,
					'IsStatic' => '0',
				),
				array(
					'ClassId' => '1',
					'Name' => 'staticChange',
					'Value' => 'null',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '1',
				),
			)
		);
		$this->assertTablesEmpty();
	}

	public function testClassConstantChanges()
	{
		$this->initFixture('ClassConstantsBefore');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassConstants',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
			)
		);
		$this->assertTableContent(
			'ClassConstants',
			array(
				array(
					'ClassId' => '1',
					'Name' => 'VALUE_STAYS',
					'Value' => '"old-value1"',
				),
				array(
					'ClassId' => '1',
					'Name' => 'VALUE_CHANGES',
					'Value' => '"old-value2"',
				),
			)
		);
		$this->assertTablesEmpty();

		$this->initFixture('ClassConstantsAfter');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassConstants',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => '[]',
				),
			)
		);
		$this->assertTableContent(
			'ClassConstants',
			array(
				array(
					'ClassId' => '1',
					'Name' => 'VALUE_STAYS',
					'Value' => '"old-value1"',
				),
				array(
					'ClassId' => '1',
					'Name' => 'VALUE_CHANGES',
					'Value' => '"new-value2"',
				),
			)
		);
		$this->assertTablesEmpty();
	}

	public function testClassMethodFlagChanges()
	{
		$this->initFixture('ClassMethodFlagsBefore');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassMethods',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '1',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
			)
		);
		$this->assertTableContent(
			'ClassMethods',
			array(
				array(
					'Id' => '1',
					'ClassId' => '1',
					'Name' => 'methodOne',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '2',
					'ClassId' => '1',
					'Name' => 'methodTwo',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PROTECTED,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '3',
					'ClassId' => '1',
					'Name' => 'methodThree',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PRIVATE,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '4',
					'ClassId' => '1',
					'Name' => 'methodFour',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '1',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '5',
					'ClassId' => '1',
					'Name' => 'methodFive',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '1',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '6',
					'ClassId' => '1',
					'Name' => 'methodSix',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '1',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '7',
					'ClassId' => '1',
					'Name' => 'methodSeven',
					'ParameterCount' => '1',
					'RequiredParameterCount' => '1',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '1',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '8',
					'ClassId' => '1',
					'Name' => 'methodEight',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '1',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '9',
					'ClassId' => '1',
					'Name' => 'methodNine',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '1',
					'ReturnType' => 'string',
				),
			)
		);
		$this->assertTableContent(
			'MethodParameters',
			array(
				array(
					'MethodId' => '7',
					'Name' => 'numbers',
					'Position' => '0',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
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

		$this->initFixture('ClassMethodFlagsAfter');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassMethods',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '1',
					'IsFinal' => '0',
					'RawRelations' => '[]',
				),
			)
		);
		$this->assertTableContent(
			'ClassMethods',
			array(
				array(
					'Id' => '1',
					'ClassId' => '1',
					'Name' => 'methodOne',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PRIVATE,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '2',
					'ClassId' => '1',
					'Name' => 'methodTwo',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '3',
					'ClassId' => '1',
					'Name' => 'methodThree',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PROTECTED,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '4',
					'ClassId' => '1',
					'Name' => 'methodFour',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '1',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '5',
					'ClassId' => '1',
					'Name' => 'methodFive',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '1',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '6',
					'ClassId' => '1',
					'Name' => 'methodSix',
					'ParameterCount' => '1',
					'RequiredParameterCount' => '1',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '1',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '7',
					'ClassId' => '1',
					'Name' => 'methodSeven',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '1',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '8',
					'ClassId' => '1',
					'Name' => 'methodEight',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '1',
					'ReturnType' => 'string',
				),
				array(
					'Id' => '9',
					'ClassId' => '1',
					'Name' => 'methodNine',
					'ParameterCount' => '0',
					'RequiredParameterCount' => '0',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '1',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
			)
		);
		$this->assertTableContent(
			'MethodParameters',
			array(
				array(
					'MethodId' => '6',
					'Name' => 'numbers',
					'Position' => '0',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
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

	public function testMethodParameterChanges()
	{
		$this->initFixture('MethodParametersBefore');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'MethodParameters',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
			)
		);
		$this->assertTableContent(
			'ClassMethods',
			array(
				array(
					'Id' => '1',
					'ClassId' => '1',
					'Name' => 'greedyMethod',
					'ParameterCount' => '8',
					'RequiredParameterCount' => '5',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '2',
					'ClassId' => '1',
					'Name' => 'variadicMethod',
					'ParameterCount' => '2',
					'RequiredParameterCount' => '2',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '1',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
			)
		);
		$this->assertTableContent(
			'MethodParameters',
			array(
				array(
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '2',
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
					'MethodId' => '2',
					'Name' => 'param_ten',
					'Position' => '1',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
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

		$this->initFixture('MethodParametersAfter');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'MethodParameters',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => '[]',
				),
			)
		);
		$this->assertTableContent(
			'ClassMethods',
			array(
				array(
					'Id' => '1',
					'ClassId' => '1',
					'Name' => 'greedyMethod',
					'ParameterCount' => '8',
					'RequiredParameterCount' => '6',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '2',
					'ClassId' => '1',
					'Name' => 'variadicMethod',
					'ParameterCount' => '2',
					'RequiredParameterCount' => '2',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '1',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
			)
		);
		$this->assertTableContent(
			'MethodParameters',
			array(
				array(
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '1',
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
					'MethodId' => '2',
					'Name' => 'param_nine',
					'Position' => '1',
					'TypeClass' => null,
					'HasType' => '0',
					'TypeName' => null,
					'AllowsNull' => '1',
					'IsArray' => '0',
					'IsCallable' => '0',
					'IsOptional' => '0',
					'IsVariadic' => '1',
					'CanBePassedByValue' => '1',
					'IsPassedByReference' => '0',
					'HasDefaultValue' => '0',
					'DefaultValue' => null,
					'DefaultConstant' => null,
				),
				array(
					'MethodId' => '2',
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

	public function testClassRelations()
	{
		$this->initFixture('ClassRelations');

		$knowledge_base = $this->expectClassRelationProcessing();

		// Repeat twice to ensure existing DB record was reused.
		for ( $i = 0; $i < 2; $i++ ) {
			$this->collectData();

			$this->assertTableContent(
				'Classes',
				array(
					array(
						'Id' => '1',
						'FileId' => '1',
						'Name' => 'UserClass',
						'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
						'IsAbstract' => '0',
						'IsFinal' => '0',
						'RawRelations' => $i === 0 ? null : '[]',
					),
					array(
						'Id' => '2',
						'FileId' => '1',
						'Name' => 'UserInterface',
						'ClassType' => (string)ClassDataCollector::TYPE_INTERFACE,
						'IsAbstract' => '0',
						'IsFinal' => '0',
						'RawRelations' => $i === 0 ? null : '[]',
					),
					array(
						'Id' => '3',
						'FileId' => '1',
						'Name' => 'UserTrait',
						'ClassType' => (string)ClassDataCollector::TYPE_TRAIT,
						'IsAbstract' => '0',
						'IsFinal' => '0',
						'RawRelations' => $i === 0 ? null : '[]',
					),
					array(
						'Id' => '4',
						'FileId' => '1',
						'Name' => 'ClassRelations',
						'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
						'IsAbstract' => '0',
						'IsFinal' => '0',
						'RawRelations' => '[["UserClass",1,false],["UserInterface",2,false],["UserTrait",3,false]]',
					),
					array(
						'Id' => '5',
						'FileId' => '1',
						'Name' => 'ClassRelations2',
						'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
						'IsAbstract' => '0',
						'IsFinal' => '0',
						'RawRelations' => '[["Exception",1,true],["Traversable",2,true]]',
					),
				)
			);
			$this->assertTableContent(
				'ClassProperties',
				array(
					array(
						'ClassId' => '1',
						'Name' => 'userClassProperty',
						'Value' => 'null',
						'Scope' => '3',
						'IsStatic' => '0',
					),
					array(
						'ClassId' => '4',
						'Name' => 'classRelationsProperty',
						'Value' => 'null',
						'Scope' => '3',
						'IsStatic' => '0',
					),
				)
			);
			$this->assertTableContent(
				'ClassMethods',
				array(
					array(
						'Id' => '1',
						'ClassId' => '1',
						'Name' => 'userClassMethod',
						'ParameterCount' => '0',
						'RequiredParameterCount' => '0',
						'Scope' => '3',
						'IsAbstract' => '0',
						'IsFinal' => '0',
						'IsStatic' => '0',
						'IsVariadic' => '0',
						'ReturnsReference' => '0',
						'HasReturnType' => '0',
						'ReturnType' => null,
					),
					array(
						'Id' => '2',
						'ClassId' => '4',
						'Name' => 'classRelationsMethod',
						'ParameterCount' => '0',
						'RequiredParameterCount' => '0',
						'Scope' => '3',
						'IsAbstract' => '0',
						'IsFinal' => '0',
						'IsStatic' => '0',
						'IsVariadic' => '0',
						'ReturnsReference' => '0',
						'HasReturnType' => '0',
						'ReturnType' => null,
					),
				)
			);
			$this->assertTablesEmpty();
		}

		$this->dataCollector->aggregateData($knowledge_base);

		$this->assertTableContent(
			'ClassRelations',
			array(
				array(
					'ClassId' => '4',
					'RelatedClass' => 'UserClass',
					'RelatedClassId' => '1',
					'RelationType' => (string)ClassDataCollector::RELATION_TYPE_EXTENDS,
				),
				array(
					'ClassId' => '4',
					'RelatedClass' => 'UserInterface',
					'RelatedClassId' => '2',
					'RelationType' => (string)ClassDataCollector::RELATION_TYPE_IMPLEMENTS,
				),
				array(
					'ClassId' => '4',
					'RelatedClass' => 'UserTrait',
					'RelatedClassId' => '3',
					'RelationType' => (string)ClassDataCollector::RELATION_TYPE_USES,
				),
				array(
					'ClassId' => '5',
					'RelatedClass' => 'Exception',
					'RelatedClassId' => '0',
					'RelationType' => (string)ClassDataCollector::RELATION_TYPE_EXTENDS,
				),
				array(
					'ClassId' => '5',
					'RelatedClass' => 'Traversable',
					'RelatedClassId' => '0',
					'RelationType' => (string)ClassDataCollector::RELATION_TYPE_IMPLEMENTS,
				),
			)
		);
	}

	public function testClassRelationChanges()
	{
		$this->initFixture('ClassRelationsBefore');

		$knowledge_base = $this->expectClassRelationProcessing();

		$this->collectData();
		$this->dataCollector->aggregateData($knowledge_base);

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassRelationChangesA',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
				array(
					'Id' => '2',
					'FileId' => '1',
					'Name' => 'ClassRelationChangesB',
					'ClassType' => (string)ClassDataCollector::TYPE_INTERFACE,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
				array(
					'Id' => '3',
					'FileId' => '1',
					'Name' => 'ClassRelationChangesC',
					'ClassType' => (string)ClassDataCollector::TYPE_TRAIT,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
				array(
					'Id' => '4',
					'FileId' => '1',
					'Name' => 'ClassRelationChangesD',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
			)
		);
		$this->assertTableContent(
			'ClassRelations',
			array(
				array(
					'ClassId' => '4',
					'RelatedClass' => 'ClassRelationChangesA',
					'RelatedClassId' => '1',
					'RelationType' => (string)ClassDataCollector::RELATION_TYPE_EXTENDS,
				),
				array(
					'ClassId' => '4',
					'RelatedClass' => 'ClassRelationChangesB',
					'RelatedClassId' => '2',
					'RelationType' => (string)ClassDataCollector::RELATION_TYPE_IMPLEMENTS,
				),
				array(
					'ClassId' => '4',
					'RelatedClass' => 'ClassRelationChangesC',
					'RelatedClassId' => '3',
					'RelationType' => (string)ClassDataCollector::RELATION_TYPE_USES,
				),
			)
		);

		$this->assertTablesEmpty();

		$this->classMapping['ClassRelationChangesA'] = 'ClassRelationsAfter.php';
		$this->classMapping['ClassRelationChangesB'] = 'ClassRelationsAfter.php';
		$this->classMapping['ClassRelationChangesC'] = 'ClassRelationsAfter.php';
		$this->classMapping['ClassRelationChangesD'] = 'ClassRelationsAfter.php';

		$this->initFixture('ClassRelationsAfter');
		$this->collectData();
		$this->dataCollector->aggregateData($knowledge_base);

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'ClassRelationChangesA',
					'ClassType' => (string)ClassDataCollector::TYPE_TRAIT,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
				array(
					'Id' => '2',
					'FileId' => '1',
					'Name' => 'ClassRelationChangesB',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
				array(
					'Id' => '3',
					'FileId' => '1',
					'Name' => 'ClassRelationChangesC',
					'ClassType' => (string)ClassDataCollector::TYPE_INTERFACE,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
				array(
					'Id' => '4',
					'FileId' => '1',
					'Name' => 'ClassRelationChangesD',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
			)
		);
		$this->assertTableContent(
			'ClassRelations',
			array(
				array(
					'ClassId' => '4',
					'RelatedClass' => 'ClassRelationChangesA',
					'RelatedClassId' => '1',
					'RelationType' => (string)ClassDataCollector::RELATION_TYPE_USES,
				),
				array(
					'ClassId' => '4',
					'RelatedClass' => 'ClassRelationChangesB',
					'RelatedClassId' => '2',
					'RelationType' => (string)ClassDataCollector::RELATION_TYPE_EXTENDS,
				),
				array(
					'ClassId' => '4',
					'RelatedClass' => 'ClassRelationChangesC',
					'RelatedClassId' => '3',
					'RelationType' => (string)ClassDataCollector::RELATION_TYPE_IMPLEMENTS,
				),
			)
		);

		$this->assertTablesEmpty();
	}

	public function testDynamicDataRemoval()
	{
		$this->initFixture('DynamicDataRemoval');

		$knowledge_base = $this->expectClassRelationProcessing();

		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'DynamicDataRemoval',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => '[["stdClass",1,true]]',
				),
				array(
					'Id' => '2',
					'FileId' => '1',
					'Name' => 'DynamicDataRemovalTwo',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => null,
				),
			)
		);
		$this->assertTableContent(
			'ClassConstants',
			array(
				array(
					'ClassId' => '1',
					'Name' => 'CONSTANT_ONE',
					'Value' => '1',
				),
				array(
					'ClassId' => '1',
					'Name' => 'CONSTANT_TWO',
					'Value' => '2',
				),
				array(
					'ClassId' => '2',
					'Name' => 'CONSTANT_ONE',
					'Value' => '1',
				),
				array(
					'ClassId' => '2',
					'Name' => 'CONSTANT_TWO',
					'Value' => '2',
				),
			)
		);
		$this->assertTableContent(
			'ClassProperties',
			array(
				array(
					'ClassId' => '1',
					'Name' => 'propertyOne',
					'Value' => 'null',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '0',
				),
				array(
					'ClassId' => '1',
					'Name' => 'propertyTwo',
					'Value' => 'null',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '0',
				),
				array(
					'ClassId' => '2',
					'Name' => 'propertyOne',
					'Value' => 'null',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '0',
				),
				array(
					'ClassId' => '2',
					'Name' => 'propertyTwo',
					'Value' => 'null',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '0',
				),
			)
		);
		$this->assertTableContent(
			'ClassMethods',
			array(
				array(
					'Id' => '1',
					'ClassId' => '1',
					'Name' => 'methodOne',
					'ParameterCount' => '2',
					'RequiredParameterCount' => '2',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '2',
					'ClassId' => '1',
					'Name' => 'methodTwo',
					'ParameterCount' => '2',
					'RequiredParameterCount' => '2',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '3',
					'ClassId' => '2',
					'Name' => 'methodOne',
					'ParameterCount' => '2',
					'RequiredParameterCount' => '2',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
				array(
					'Id' => '4',
					'ClassId' => '2',
					'Name' => 'methodTwo',
					'ParameterCount' => '2',
					'RequiredParameterCount' => '2',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
			)
		);
		$this->assertTableContent(
			'MethodParameters',
			array(
				array(
					'MethodId' => '1',
					'Name' => 'method_param1',
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
					'MethodId' => '1',
					'Name' => 'method_param2',
					'Position' => '1',
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
					'MethodId' => '2',
					'Name' => 'method_param1',
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
					'MethodId' => '2',
					'Name' => 'method_param2',
					'Position' => '1',
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
					'MethodId' => '3',
					'Name' => 'method_param1',
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
					'MethodId' => '3',
					'Name' => 'method_param2',
					'Position' => '1',
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
					'MethodId' => '4',
					'Name' => 'method_param1',
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
					'MethodId' => '4',
					'Name' => 'method_param2',
					'Position' => '1',
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

		$this->dataCollector->aggregateData($knowledge_base);

		$this->assertTableContent(
			'ClassRelations',
			array(
				array(
					'ClassId' => '1',
					'RelatedClass' => 'stdClass',
					'RelatedClassId' => '0',
					'RelationType' => '1',
				),
			)
		);

		$this->initFixture('DynamicDataRemovalDone');
		$this->collectData();

		$this->assertTableContent(
			'Classes',
			array(
				array(
					'Id' => '1',
					'FileId' => '1',
					'Name' => 'DynamicDataRemoval',
					'ClassType' => (string)ClassDataCollector::TYPE_CLASS,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'RawRelations' => '[]',
				),
			)
		);
		$this->assertTableContent(
			'ClassConstants',
			array(
				array(
					'ClassId' => '1',
					'Name' => 'CONSTANT_ONE',
					'Value' => '1',
				),
			)
		);
		$this->assertTableContent(
			'ClassProperties',
			array(
				array(
					'ClassId' => '1',
					'Name' => 'propertyOne',
					'Value' => 'null',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsStatic' => '0',
				),
			)
		);
		$this->assertTableContent(
			'ClassMethods',
			array(
				array(
					'Id' => '1',
					'ClassId' => '1',
					'Name' => 'methodOne',
					'ParameterCount' => '1',
					'RequiredParameterCount' => '1',
					'Scope' => (string)ClassDataCollector::SCOPE_PUBLIC,
					'IsAbstract' => '0',
					'IsFinal' => '0',
					'IsStatic' => '0',
					'IsVariadic' => '0',
					'ReturnsReference' => '0',
					'HasReturnType' => '0',
					'ReturnType' => null,
				),
			)
		);
		$this->assertTableContent(
			'MethodParameters',
			array(
				array(
					'MethodId' => '1',
					'Name' => 'method_param1',
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

		$this->dataCollector->aggregateData($knowledge_base);

		$this->assertTableEmpty('ClassRelations');
	}

	public function testDeleteData()
	{
		$this->initFixture('DynamicDataRemoval');

		$knowledge_base = $this->expectClassRelationProcessing();
		$this->collectData();
		$this->dataCollector->aggregateData($knowledge_base);

		$this->assertTableCount('Classes', 2);
		$this->assertTableCount('ClassConstants', 4);
		$this->assertTableCount('ClassProperties', 4);
		$this->assertTableCount('ClassMethods', 4);
		$this->assertTableCount('MethodParameters', 8);
		$this->assertTableCount('ClassRelations', 1);

		$this->dataCollector->deleteData(array($this->fileId));

		$this->assertTablesEmpty();
	}

	public function testGetStatistics()
	{
		$this->initFixture('Statistics');
		$this->collectData();

		$this->assertSame(
			array(
				'Classes' => '1',
				'Interfaces' => '2',
				'Traits' => '3',
				'Files With Multiple Classes' => 1,
			),
			$this->dataCollector->getStatistics()
		);
	}

	/**
	 * Expects class relation processing.
	 *
	 * @return KnowledgeBase
	 */
	protected function expectClassRelationProcessing()
	{
		$knowledge_base = $this->prophesize('\ConsoleHelpers\CodeInsight\KnowledgeBase\KnowledgeBase');
		$knowledge_base
			->processFile(new RegExToken('/^' . preg_quote($this->getFixturePath(''), '/') . '.*$/'))
			->willReturn($this->fileId);

		return $knowledge_base->reveal();
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
		$this->fixturePath = $this->locateFixtureClass($fixture);

		if ( !$this->fileId ) {
			$this->fileId = $this->createFileMention($this->fixturePath);
		}
	}

	/**
	 * Locates fixture classes for Reflection engine.
	 *
	 * @param string $class Class.
	 *
	 * @return string
	 */
	public function locateFixtureClass($class)
	{
		$class = str_replace('\\', '/', ltrim($class, '\\'));

		if ( isset($this->classMapping[$class]) ) {
			return $this->getFixturePath('ClassDataCollector/' . $this->classMapping[$class]);
		}

		return $this->getFixturePath('ClassDataCollector/' . $class . '.php');
	}

	/**
	 * Creates data collector.
	 *
	 * @return AbstractDataCollector
	 */
	protected function createDataCollector()
	{
		return new ClassDataCollector($this->database);
	}

}
