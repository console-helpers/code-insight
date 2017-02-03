<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker;


use Aura\Sql\ExtendedPdoInterface;
use ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector\ClassDataCollector;

class ClassChecker extends AbstractChecker
{

	const CACHE_DURATION = 3600;

	const TYPE_CLASS_DELETED = 'class.deleted';
	const TYPE_CLASS_MADE_ABSTRACT = 'class.made_abstract';
	const TYPE_CLASS_MADE_FINAL = 'class.made_final';
	const TYPE_CLASS_CONSTANT_DELETED = 'class.constant.deleted';
	const TYPE_PROPERTY_DELETED = 'property.deleted';
	const TYPE_PROPERTY_MADE_STATIC = 'property.made_static';
	const TYPE_PROPERTY_MADE_NON_STATIC = 'property.made_non_static';
	const TYPE_PROPERTY_SCOPE_REDUCED = 'property.scope_reduced';
	const TYPE_METHOD_DELETED = 'method.deleted';
	const TYPE_METHOD_MADE_ABSTRACT = 'method.made_abstract';
	const TYPE_METHOD_MADE_FINAL = 'method.made_final';
	const TYPE_METHOD_MADE_STATIC = 'method.made_static';
	const TYPE_METHOD_MADE_NON_STATIC = 'method.made_non_static';
	const TYPE_METHOD_SCOPE_REDUCED = 'method.scope_reduced';
	const TYPE_METHOD_SIGNATURE_CHANGED = 'method.signature_changed';

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
	 * Source property data.
	 *
	 * @var array
	 */
	protected $sourcePropertyData = array();

	/**
	 * Target property data.
	 *
	 * @var array
	 */
	protected $targetPropertyData = array();

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
	 * Returns backwards compatibility checker name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'class';
	}

	/**
	 * Collects backwards compatibility violations.
	 *
	 * @return void
	 */
	protected function doCheck()
	{
		$classes_sql = 'SELECT Name, Id, IsAbstract, IsFinal
						FROM Classes';
		$source_classes = $this->sourceDatabase->fetchAssoc($classes_sql);
		$target_classes = $this->targetDatabase->fetchAssoc($classes_sql);

		foreach ( $source_classes as $class_name => $source_class_data ) {
			if ( !isset($target_classes[$class_name]) ) {
				$this->addIncident(self::TYPE_CLASS_DELETED, $class_name);
				continue;
			}

			$this->sourceClassData = $source_class_data;
			$this->targetClassData = $target_classes[$class_name];

			if ( !$this->sourceClassData['IsAbstract'] && $this->targetClassData['IsAbstract'] ) {
				$this->addIncident(self::TYPE_CLASS_MADE_ABSTRACT, $class_name);
			}

			if ( !$this->sourceClassData['IsFinal'] && $this->targetClassData['IsFinal'] ) {
				$this->addIncident(self::TYPE_CLASS_MADE_FINAL, $class_name);
			}

			$this->processConstants();
			$this->processProperties();
			$this->processMethods();
		}
	}

	/**
	 * Checks constants.
	 *
	 * @return void
	 */
	protected function processConstants()
	{
		$class_name = $this->sourceClassData['Name'];

		$source_constants = $this->getConstantsRecursively($this->sourceDatabase, $this->sourceClassData['Id']);
		$target_constants = $this->getConstantsRecursively($this->targetDatabase, $this->targetClassData['Id']);

		foreach ( $source_constants as $source_constant_name => $source_constant_data ) {
			$full_constant_name = $class_name . '::' . $source_constant_name;

			// @codeCoverageIgnoreStart
			// Report incidents for processed (not inherited) constants only.
			if ( $source_constant_data['ClassId'] !== $this->sourceClassData['Id'] ) {
				continue;
			}
			// @codeCoverageIgnoreEnd

			if ( !isset($target_constants[$source_constant_name]) ) {
				$this->addIncident(self::TYPE_CLASS_CONSTANT_DELETED, $full_constant_name);
				continue;
			}
		}
	}

	/**
	 * Returns class constants.
	 *
	 * @param ExtendedPdoInterface $db       Database.
	 * @param integer              $class_id Class ID.
	 *
	 * @return array
	 */
	protected function getConstantsRecursively(ExtendedPdoInterface $db, $class_id)
	{
		$cache_key = $this->getCacheKey($db, 'class_constants[' . $class_id . ']');
		$cached_value = $this->cache->fetch($cache_key);

		if ( $cached_value === false ) {
			$sql = 'SELECT Name, ClassId
					FROM ClassConstants
					WHERE ClassId = :class_id';
			$cached_value = $db->fetchAssoc($sql, array('class_id' => $class_id));

			foreach ( $this->getClassRelations($db, $class_id) as $related_class_id => $related_class_name ) {
				foreach ( $this->getConstantsRecursively($db, $related_class_id) as $name => $data ) {
					// @codeCoverageIgnoreStart
					if ( !array_key_exists($name, $cached_value) ) {
						$cached_value[$name] = $data;
					}
					// @codeCoverageIgnoreEnd
				}
			}

			// TODO: Cache for longer period, when DB update will invalidate associated cache.
			$this->cache->save($cache_key, $cached_value, self::CACHE_DURATION);
		}

		return $cached_value;
	}

	/**
	 * Checks properties.
	 *
	 * @return void
	 */
	protected function processProperties()
	{
		$class_name = $this->sourceClassData['Name'];
		$source_properties = $this->getPropertiesRecursively(
			$this->sourceDatabase,
			$this->sourceClassData['Id'],
			$this->coveredScopes()
		);
		$target_properties = $this->getPropertiesRecursively($this->targetDatabase, $this->targetClassData['Id'], '');

		foreach ( $source_properties as $source_property_name => $source_property_data ) {
			$full_property_name = $class_name . '::$' . $source_property_name;

			// Report incidents for processed (not inherited) properties only.
			if ( $source_property_data['ClassId'] !== $this->sourceClassData['Id'] ) {
				continue;
			}

			if ( !isset($target_properties[$source_property_name]) ) {
				$this->addIncident(self::TYPE_PROPERTY_DELETED, $full_property_name);
				continue;
			}

			$this->sourcePropertyData = $source_property_data;
			$this->targetPropertyData = $target_properties[$source_property_name];

			$this->processProperty();
		}
	}

	/**
	 * Returns class properties.
	 *
	 * @param ExtendedPdoInterface $db       Database.
	 * @param integer              $class_id Class ID.
	 * @param string               $scopes   Scopes.
	 *
	 * @return array
	 */
	protected function getPropertiesRecursively(ExtendedPdoInterface $db, $class_id, $scopes)
	{
		$cache_key = $this->getCacheKey($db, 'class_properties[' . $class_id . ']_scopes[' . ($scopes ?: '*') . ']');
		$cached_value = $this->cache->fetch($cache_key);

		if ( $cached_value === false ) {
			$sql = 'SELECT Name, Scope, IsStatic, ClassId
					FROM ClassProperties
					WHERE ClassId = :class_id';

			if ( $scopes ) {
				$sql .= ' AND Scope IN (' . $scopes . ')';
			}

			$cached_value = $db->fetchAssoc($sql, array('class_id' => $class_id));

			foreach ( $this->getClassRelations($db, $class_id) as $related_class_id => $related_class_name ) {
				foreach ( $this->getPropertiesRecursively($db, $related_class_id, $scopes) as $name => $data ) {
					if ( !array_key_exists($name, $cached_value) ) {
						$cached_value[$name] = $data;
					}
				}
			}

			// TODO: Cache for longer period, when DB update will invalidate associated cache.
			$this->cache->save($cache_key, $cached_value, self::CACHE_DURATION);
		}

		return $cached_value;
	}

	/**
	 * Processes property.
	 *
	 * @return void
	 */
	protected function processProperty()
	{
		$class_name = $this->sourceClassData['Name'];
		$property_name = $this->sourcePropertyData['Name'];

		$full_property_name = $class_name . '::$' . $property_name;

		if ( !$this->sourcePropertyData['IsStatic'] && $this->targetPropertyData['IsStatic'] ) {
			$this->addIncident(self::TYPE_PROPERTY_MADE_STATIC, $full_property_name);
		}

		if ( $this->sourcePropertyData['IsStatic'] && !$this->targetPropertyData['IsStatic'] ) {
			$this->addIncident(self::TYPE_PROPERTY_MADE_NON_STATIC, $full_property_name);
		}

		if ( $this->sourcePropertyData['Scope'] > $this->targetPropertyData['Scope'] ) {
			$this->addIncident(
				self::TYPE_PROPERTY_SCOPE_REDUCED,
				$full_property_name,
				$this->getScopeName($this->sourcePropertyData['Scope']),
				$this->getScopeName($this->targetPropertyData['Scope'])
			);
		}
	}

	/**
	 * Checks methods.
	 *
	 * @return void
	 */
	protected function processMethods()
	{
		$class_name = $this->sourceClassData['Name'];
		$source_methods = $this->getMethodsRecursively(
			$this->sourceDatabase,
			$this->sourceClassData['Id'],
			$this->coveredScopes()
		);
		$target_methods = $this->getMethodsRecursively($this->targetDatabase, $this->targetClassData['Id'], '');

		foreach ( $source_methods as $source_method_name => $source_method_data ) {
			$target_method_name = $source_method_name;
			$full_method_name = $class_name . '::' . $source_method_name;

			// Ignore PHP4 constructor rename into PHP5 constructor.
			if ( !isset($target_methods[$target_method_name]) && $target_method_name === $class_name ) {
				$target_method_name = '__construct';
			}

			// Ignore PHP5 constructor rename into PHP4 constructor.
			if ( !isset($target_methods[$target_method_name]) && $target_method_name === '__construct' ) {
				$target_method_name = $class_name;
			}

			// Report incidents for processed (not inherited) methods only.
			if ( $source_method_data['ClassId'] !== $this->sourceClassData['Id'] ) {
				continue;
			}

			if ( !isset($target_methods[$target_method_name]) ) {
				$this->addIncident(self::TYPE_METHOD_DELETED, $full_method_name);
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
	 * Returns class methods.
	 *
	 * @param ExtendedPdoInterface $db       Database.
	 * @param integer              $class_id Class ID.
	 * @param string               $scopes   Scopes.
	 *
	 * @return array
	 */
	protected function getMethodsRecursively(ExtendedPdoInterface $db, $class_id, $scopes)
	{
		$cache_key = $this->getCacheKey($db, 'class_methods[' . $class_id . ']_scopes[' . ($scopes ?: '*') . ']');
		$cached_value = $this->cache->fetch($cache_key);

		if ( $cached_value === false ) {
			$sql = 'SELECT Name, Id, Scope, IsAbstract, IsFinal, IsStatic, ClassId
					FROM ClassMethods
					WHERE ClassId = :class_id';

			if ( $scopes ) {
				$sql .= ' AND Scope IN (' . $scopes . ')';
			}

			$cached_value = $db->fetchAssoc($sql, array('class_id' => $class_id));

			foreach ( $this->getClassRelations($db, $class_id) as $related_class_id => $related_class_name ) {
				foreach ( $this->getMethodsRecursively($db, $related_class_id, $scopes) as $name => $data ) {
					if ( !array_key_exists($name, $cached_value) ) {
						$cached_value[$name] = $data;
					}
				}
			}

			// TODO: Cache for longer period, when DB update will invalidate associated cache.
			$this->cache->save($cache_key, $cached_value, self::CACHE_DURATION);
		}

		return $cached_value;
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
			$this->addIncident(self::TYPE_METHOD_MADE_ABSTRACT, $full_method_name);
		}

		if ( !$this->sourceMethodData['IsFinal'] && $this->targetMethodData['IsFinal'] ) {
			$this->addIncident(self::TYPE_METHOD_MADE_FINAL, $full_method_name);
		}

		if ( !$this->sourceMethodData['IsStatic'] && $this->targetMethodData['IsStatic'] ) {
			$this->addIncident(self::TYPE_METHOD_MADE_STATIC, $full_method_name);
		}

		if ( $this->sourceMethodData['IsStatic'] && !$this->targetMethodData['IsStatic'] ) {
			$this->addIncident(self::TYPE_METHOD_MADE_NON_STATIC, $full_method_name);
		}

		if ( $this->sourceMethodData['ParameterSignature'] !== $this->targetMethodData['ParameterSignature'] ) {
			$this->addIncident(
				self::TYPE_METHOD_SIGNATURE_CHANGED,
				$full_method_name,
				$this->sourceMethodData['ParameterSignature'],
				$this->targetMethodData['ParameterSignature']
			);
		}

		if ( $this->sourceMethodData['Scope'] > $this->targetMethodData['Scope'] ) {
			$this->addIncident(
				self::TYPE_METHOD_SCOPE_REDUCED,
				$full_method_name,
				$this->getScopeName($this->sourceMethodData['Scope']),
				$this->getScopeName($this->targetMethodData['Scope'])
			);
		}
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

	/**
	 * Scopes covered by backwards compatibility checks.
	 *
	 * @return string
	 */
	protected function coveredScopes()
	{
		// Ignore changes in protected class members for "final" classes.
		if ( $this->sourceClassData['IsFinal'] && $this->targetClassData['IsFinal'] ) {
			return ClassDataCollector::SCOPE_PUBLIC;
		}

		return ClassDataCollector::SCOPE_PUBLIC . ',' . ClassDataCollector::SCOPE_PROTECTED;
	}

	/**
	 * Returns class constants.
	 *
	 * @param ExtendedPdoInterface $db       Database.
	 * @param integer              $class_id Class ID.
	 *
	 * @return array
	 */
	protected function getClassRelations(ExtendedPdoInterface $db, $class_id)
	{
		$cache_key = $this->getCacheKey($db, 'class_relations[' . $class_id . ']');
		$cached_value = $this->cache->fetch($cache_key);

		if ( $cached_value === false ) {
			$sql = 'SELECT RelatedClassId, RelatedClass
					FROM ClassRelations
					WHERE ClassId = :class_id AND RelatedClassId <> 0';
			$cached_value = $db->fetchPairs($sql, array('class_id' => $class_id));

			// TODO: Cache for longer period, when DB update will invalidate associated cache.
			$this->cache->save($cache_key, $cached_value, self::CACHE_DURATION);
		}

		return $cached_value;
	}

}
