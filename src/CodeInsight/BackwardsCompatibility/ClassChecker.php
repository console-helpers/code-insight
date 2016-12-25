<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\BackwardsCompatibility;


use Aura\Sql\ExtendedPdoInterface;
use ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector\ClassDataCollector;

class ClassChecker extends AbstractChecker
{

	/**
	 * Source class data.
	 *
	 * @var array
	 */
	protected $sourceClassData = array();

	/**
	 * Target class data.
	 *
	 * @var array
	 */
	protected $targetClassData = array();

	/**
	 * Source method data.
	 *
	 * @var array
	 */
	protected $sourceMethodData = array();

	/**
	 * Target method data.
	 *
	 * @var array
	 */
	protected $targetMethodData = array();

	/**
	 * ClassChecker constructor.
	 */
	public function __construct()
	{
		$this->incidents = array(
			'Class Deleted' => array(),
			'Class Made Abstract' => array(),
			'Class Made Final' => array(),
			'Method Deleted' => array(),
			'Method Made Abstract' => array(),
			'Method Made Final' => array(),
			'Method Scope Reduced' => array(),
			'Method Signature Changed' => array(),
		);
	}

	/**
	 * Returns backwards compatibility checker name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'class';
	}

	/**
	 * Checks backwards compatibility and returns violations by category.
	 *
	 * @param ExtendedPdoInterface $source_db Source DB.
	 * @param ExtendedPdoInterface $target_db Target DB.
	 *
	 * @return array
	 */
	public function check(ExtendedPdoInterface $source_db, ExtendedPdoInterface $target_db)
	{
		$this->sourceDatabase = $source_db;
		$this->targetDatabase = $target_db;

		$classes_sql = 'SELECT Name, Id, IsAbstract, IsFinal 
						FROM Classes';
		$source_classes = $this->sourceDatabase->fetchAssoc($classes_sql);
		$target_classes = $this->targetDatabase->fetchAssoc($classes_sql);

		foreach ( $source_classes as $class_name => $source_class_data ) {
			if ( !isset($target_classes[$class_name]) ) {
				$this->addIncident('Class Deleted', $class_name);
				continue;
			}

			$this->sourceClassData = $source_class_data;
			$this->targetClassData = $target_classes[$class_name];

			if ( !$this->sourceClassData['IsAbstract'] && $this->targetClassData['IsAbstract'] ) {
				$this->addIncident('Class Made Abstract', $class_name);
			}

			if ( !$this->sourceClassData['IsFinal'] && $this->targetClassData['IsFinal'] ) {
				$this->addIncident('Class Made Final', $class_name);
			}

			$this->processMethods();
		}

		return array_filter($this->incidents);
	}

	/**
	 * Checks methods.
	 *
	 * @return void
	 */
	protected function processMethods()
	{
		$class_name = $this->sourceClassData['Name'];
		$source_methods = $this->_getSourceMethods($this->sourceClassData['Id']);
		$target_methods = $this->_getTargetMethods($this->targetClassData['Id']);

		foreach ( $source_methods as $source_method_name => $source_method_data ) {
			$target_method_name = $source_method_name;
			$full_method_name = $class_name . '::' . $source_method_name;

			// Ignore PHP4 constructor rename into PHP5 constructor.
			if ( !isset($target_methods[$target_method_name]) && $target_method_name === $class_name ) {
				$target_method_name = '__construct';
			}

			if ( !isset($target_methods[$target_method_name]) ) {
				$this->addIncident('Method Deleted', $full_method_name);
				continue;
			}

			$this->sourceMethodData = $source_method_data;
			$this->sourceMethodData['ParameterSignature'] = $this->getMethodParameterSignature(
				$this->sourceDatabase,
				$this->sourceMethodData['Id']
			);

			$this->targetMethodData = $target_methods[$target_method_name];
			$this->targetMethodData['ParameterSignature'] = $this->getMethodParameterSignature(
				$this->targetDatabase,
				$this->targetMethodData['Id']
			);

			$this->processMethod();
		}
	}

	/**
	 * Returns source methods.
	 *
	 * @param integer $class_id Class ID.
	 *
	 * @return array
	 */
	private function _getSourceMethods($class_id)
	{
		$scopes = ClassDataCollector::SCOPE_PUBLIC . ',' . ClassDataCollector::SCOPE_PROTECTED;
		$sql = 'SELECT Name, Id, Scope, IsAbstract, IsFinal
				FROM ClassMethods
				WHERE ClassId = :class_id AND Scope IN (' . $scopes . ')';

		return $this->sourceDatabase->fetchAssoc($sql, array('class_id' => $class_id));
	}

	/**
	 * Returns target methods.
	 *
	 * @param integer $class_id Class ID.
	 *
	 * @return array
	 */
	private function _getTargetMethods($class_id)
	{
		$sql = 'SELECT Name, Id, Scope, IsAbstract, IsFinal
				FROM ClassMethods
				WHERE ClassId = :class_id';

		return $this->targetDatabase->fetchAssoc($sql, array('class_id' => $class_id));
	}

	/**
	 * Processes method.
	 *
	 * @return void
	 */
	protected function processMethod()
	{
		$class_name = $this->sourceClassData['Name'];
		$method_name = $this->sourceMethodData['Name'];

		$full_method_name = $class_name . '::' . $method_name;

		if ( !$this->sourceMethodData['IsAbstract'] && $this->targetMethodData['IsAbstract'] ) {
			$this->addIncident('Method Made Abstract', $full_method_name);
		}

		if ( !$this->sourceMethodData['IsFinal'] && $this->targetMethodData['IsFinal'] ) {
			$this->addIncident('Method Made Final', $full_method_name);
		}

		if ( $this->sourceMethodData['ParameterSignature'] !== $this->targetMethodData['ParameterSignature'] ) {
			$this->addIncident(
				'Method Signature Changed',
				$full_method_name,
				$this->sourceMethodData['ParameterSignature'],
				$this->targetMethodData['ParameterSignature']
			);
		}

		if ( $this->sourceMethodData['Scope'] > $this->targetMethodData['Scope'] ) {
			$this->addIncident(
				'Method Scope Reduced',
				$full_method_name,
				$this->getScopeName($this->sourceMethodData['Scope']),
				$this->getScopeName($this->targetMethodData['Scope'])
			);
		}
	}

	/**
	 * Calculates method parameter signature.
	 *
	 * @param ExtendedPdoInterface $db        Database.
	 * @param integer              $method_id Method ID.
	 *
	 * @return integer
	 */
	protected function getMethodParameterSignature(ExtendedPdoInterface $db, $method_id)
	{
		$sql = 'SELECT *
				FROM MethodParameters
				WHERE MethodId = :method_id
				ORDER BY Position ASC';
		$method_parameters = $db->fetchAll($sql, array('method_id' => $method_id));

		$hash_parts = array();

		foreach ( $method_parameters as $method_parameter_data ) {
			$hash_parts[] = $this->paramToString($method_parameter_data);
		}

		return implode(', ', $hash_parts);
	}

	/**
	 * Returns scope name.
	 *
	 * @param integer $scope Scope.
	 *
	 * @return string
	 */
	protected function getScopeName($scope)
	{
		$mapping = array(
			ClassDataCollector::SCOPE_PRIVATE => 'private',
			ClassDataCollector::SCOPE_PROTECTED => 'protected',
			ClassDataCollector::SCOPE_PUBLIC => 'public',
		);

		return $mapping[$scope];
	}

}
