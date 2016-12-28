<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\KnowledgeBase\DataCollector;


use ConsoleHelpers\CodeInsight\KnowledgeBase\KnowledgeBase;
use Go\ParserReflection\ReflectionEngine;
use Go\ParserReflection\ReflectionFileNamespace;

class ClassDataCollector extends AbstractDataCollector
{

	const SCOPE_PRIVATE = 1;

	const SCOPE_PROTECTED = 2;

	const SCOPE_PUBLIC = 3;

	const TYPE_CLASS = 1;

	const TYPE_INTERFACE = 2;

	const TYPE_TRAIT = 3;

	const RELATION_TYPE_EXTENDS = 1;

	const RELATION_TYPE_IMPLEMENTS = 2;

	const RELATION_TYPE_USES = 3;

	/**
	 * Collect data from a namespace.
	 *
	 * @param integer                 $file_id   File id.
	 * @param ReflectionFileNamespace $namespace Namespace.
	 *
	 * @return void
	 */
	public function collectData($file_id, ReflectionFileNamespace $namespace)
	{
		$found_classes = array();

		foreach ( $namespace->getClasses() as $class ) {
			$found_classes[] = $class->getName();
			$this->processClass($file_id, $class);
		}

		if ( $found_classes ) {
			// FIXME: Would delete classes outside given namespace in same file.
			$sql = 'SELECT Id
					FROM Classes
					WHERE FileId = :file_id AND Name NOT IN (:classes)';
			$delete_classes = $this->db->fetchCol($sql, array(
				'file_id' => $file_id,
				'classes' => $found_classes,
			));

			foreach ( $delete_classes as $delete_class_id ) {
				$this->deleteClass($delete_class_id);
			}
		}
		else {
			$this->deleteData(array($file_id));
		}
	}

	/**
	 * Aggregate previously collected data.
	 *
	 * @param KnowledgeBase $knowledge_base Knowledge base.
	 *
	 * @return void
	 */
	public function aggregateData(KnowledgeBase $knowledge_base)
	{
		parent::aggregateData($knowledge_base);

		$this->processClassRawRelations($knowledge_base);
	}

	/**
	 * Delete previously collected data for a files.
	 *
	 * @param array $file_ids File IDs.
	 *
	 * @return void
	 */
	public function deleteData(array $file_ids)
	{
		$sql = 'SELECT Id
				FROM Classes
				WHERE FileId IN (:file_ids)';
		$delete_classes = $this->db->fetchCol($sql, array(
			'file_ids' => $file_ids,
		));

		foreach ( $delete_classes as $delete_class_id ) {
			$this->deleteClass($delete_class_id);
		}
	}

	/**
	 * Returns statistics about the code.
	 *
	 * @return array
	 */
	public function getStatistics()
	{
		$ret = array();

		$sql = 'SELECT ClassType, COUNT(*)
				FROM Classes
				GROUP BY ClassType';
		$classes_count = $this->db->fetchPairs($sql);

		foreach ( $classes_count as $class_type => $class_count ) {
			$title = 'Unknowns';

			if ( $class_type === self::TYPE_CLASS ) {
				$title = 'Classes';
			}
			elseif ( $class_type === self::TYPE_INTERFACE ) {
				$title = 'Interfaces';
			}
			elseif ( $class_type === self::TYPE_TRAIT ) {
				$title = 'Traits';
			}

			$ret[$title] = $class_count;
		}

		$sql = 'SELECT FileId
				FROM Classes
				GROUP BY FileId
				HAVING COUNT(*) > 1';
		$ret['Files With Multiple Classes'] = count($this->db->fetchCol($sql));

		return $ret;
	}

	/**
	 * Processes class.
	 *
	 * @param integer          $file_id File ID.
	 * @param \ReflectionClass $class   Class.
	 *
	 * @return void
	 */
	protected function processClass($file_id, \ReflectionClass $class)
	{
		$sql = 'SELECT Id
				FROM Classes
				WHERE FileId = :file_id AND Name = :name';
		$class_id = $this->db->fetchValue($sql, array(
			'file_id' => $file_id,
			'name' => $class->getName(),
		));

		$raw_class_relations = $this->getRawClassRelations($class);

		if ( $class_id === false ) {
			$sql = 'INSERT INTO Classes (Name, ClassType, IsAbstract, IsFinal, FileId, RawRelations)
					VALUES (:name, :class_type, :is_abstract, :is_final, :file_id, :raw_relations)';

			$this->db->perform(
				$sql,
				array(
					'name' => $class->getName(),
					'class_type' => $this->getClassType($class),
					'is_abstract' => $class->isTrait() ? 0 : (int)$class->isAbstract(),
					'is_final' => (int)$class->isFinal(),
					'file_id' => $file_id,
					'raw_relations' => $raw_class_relations ? json_encode($raw_class_relations) : null,
				)
			);

			$class_id = $this->db->lastInsertId();
		}
		else {
			$sql = 'UPDATE Classes
					SET	ClassType = :class_type,
						IsAbstract = :is_abstract,
						IsFinal = :is_final,
						RawRelations = :raw_relations
					WHERE Id = :class_id';

			// Always store relations as-is to detect fact, when all relations are removed.
			$this->db->perform(
				$sql,
				array(
					'class_type' => $this->getClassType($class),
					'is_abstract' => $class->isTrait() ? 0 : (int)$class->isAbstract(),
					'is_final' => (int)$class->isFinal(),
					'raw_relations' => json_encode($raw_class_relations),
					'class_id' => $class_id,
				)
			);
		}

		$this->processClassConstants($class_id, $class);
		$this->processClassProperties($class_id, $class);
		$this->processClassMethods($class_id, $class);
	}

	/**
	 * Returns class type.
	 *
	 * @param \ReflectionClass $class Class.
	 *
	 * @return integer
	 */
	protected function getClassType(\ReflectionClass $class)
	{
		if ( $class->isInterface() ) {
			return self::TYPE_INTERFACE;
		}

		if ( $class->isTrait() ) {
			return self::TYPE_TRAIT;
		}

		return self::TYPE_CLASS;
	}

	/**
	 * Get relations.
	 *
	 * @param \ReflectionClass $class Class.
	 *
	 * @return array
	 */
	protected function getRawClassRelations(\ReflectionClass $class)
	{
		$raw_relations = array();
		$parent_class = $class->getParentClass();

		if ( $parent_class ) {
			$raw_relations[] = array(
				$parent_class->getName(),
				self::RELATION_TYPE_EXTENDS,
				$parent_class->isInternal(),
			);
		}

		foreach ( $class->getInterfaces() as $interface ) {
			$raw_relations[] = array(
				$interface->getName(),
				self::RELATION_TYPE_IMPLEMENTS,
				$interface->isInternal(),
			);
		}

		foreach ( $class->getTraits() as $trait ) {
			$raw_relations[] = array(
				$trait->getName(),
				self::RELATION_TYPE_USES,
				$trait->isInternal(),
			);
		}

		return $raw_relations;
	}

	/**
	 * Deletes a class.
	 *
	 * @param integer $class_id Class ID.
	 *
	 * @return void
	 */
	protected function deleteClass($class_id)
	{
		$sql = 'DELETE FROM Classes WHERE Id = :class_id';
		$this->db->perform($sql, array('class_id' => $class_id));

		$sql = 'DELETE FROM ClassConstants WHERE ClassId = :class_id';
		$this->db->perform($sql, array('class_id' => $class_id));

		$sql = 'DELETE FROM ClassProperties WHERE ClassId = :class_id';
		$this->db->perform($sql, array('class_id' => $class_id));

		$this->deleteClassMethods($class_id, array());

		$sql = 'DELETE FROM ClassRelations WHERE ClassId = :class_id';
		$this->db->perform($sql, array('class_id' => $class_id));

		$sql = 'DELETE FROM ClassRelations WHERE RelatedClassId = :class_id';
		$this->db->perform($sql, array('class_id' => $class_id));
	}

	/**
	 * Processes class constants.
	 *
	 * @param integer          $class_id Class ID.
	 * @param \ReflectionClass $class    Class.
	 *
	 * @return void
	 */
	protected function processClassConstants($class_id, \ReflectionClass $class)
	{
		// TODO: Find a way, how to get only constants from current class without related classes.
		$constants = $class->getConstants();

		$sql = 'SELECT Name
				FROM ClassConstants
				WHERE ClassId = :class_id';
		$old_constants = $this->db->fetchCol($sql, array(
			'class_id' => $class_id,
		));

		$insert_sql = '	INSERT INTO ClassConstants (ClassId, Name, Value)
						VALUES (:class_id, :name, :value)';
		$update_sql = '	UPDATE ClassConstants
						SET Value = :value
						WHERE ClassId = :class_id AND Name = :name';

		foreach ( $constants as $constant_name => $constant_value ) {
			$this->db->perform(
				in_array($constant_name, $old_constants) ? $update_sql : $insert_sql,
				array(
					'class_id' => $class_id,
					'name' => $constant_name,
					'value' => json_encode($constant_value),
				)
			);
		}

		$delete_constants = array_diff($old_constants, array_keys($constants));

		if ( $delete_constants ) {
			$sql = 'DELETE FROM ClassConstants
					WHERE ClassId = :class_id AND Name IN (:names)';
			$this->db->perform($sql, array(
				'class_id' => $class_id,
				'names' => $delete_constants,
			));
		}
	}

	/**
	 * Processes class properties.
	 *
	 * @param integer          $class_id Class ID.
	 * @param \ReflectionClass $class    Class.
	 *
	 * @return void
	 */
	protected function processClassProperties($class_id, \ReflectionClass $class)
	{
		$sql = 'SELECT Name
				FROM ClassProperties
				WHERE ClassId = :class_id';
		$old_properties = $this->db->fetchCol($sql, array(
			'class_id' => $class_id,
		));

		$insert_sql = '	INSERT INTO ClassProperties (ClassId, Name, Value, Scope, IsStatic)
						VALUES (:class_id, :name, :value, :scope, :is_static)';
		$update_sql = '	UPDATE ClassProperties
						SET	Value = :value,
							Scope = :scope,
							IsStatic = :is_static
						WHERE ClassId = :class_id AND Name = :name';

		$new_properties = array();
		$property_defaults = $class->getDefaultProperties();
		$static_properties = $class->getStaticProperties();
		$class_name = $class->getName();

		foreach ( $class->getProperties() as $property ) {
			if ( $property->class !== $class_name ) {
				continue;
			}

			$property_name = $property->getName();
			$property_value = isset($property_defaults[$property_name]) ? $property_defaults[$property_name] : null;
			$new_properties[] = $property_name;

			$this->db->perform(
				in_array($property_name, $old_properties) ? $update_sql : $insert_sql,
				array(
					'class_id' => $class_id,
					'name' => $property_name,
					'value' => json_encode($property_value),
					'scope' => $this->getPropertyScope($property),
					'is_static' => (int)array_key_exists($property_name, $static_properties),
				)
			);
		}

		$delete_properties = array_diff($old_properties, $new_properties);

		if ( $delete_properties ) {
			$sql = 'DELETE FROM ClassProperties
					WHERE ClassId = :class_id AND Name IN (:names)';
			$this->db->perform($sql, array(
				'class_id' => $class_id,
				'names' => $delete_properties,
			));
		}
	}

	/**
	 * Returns property scope.
	 *
	 * @param \ReflectionProperty $property Property.
	 *
	 * @return integer
	 */
	protected function getPropertyScope(\ReflectionProperty $property)
	{
		if ( $property->isPrivate() ) {
			return self::SCOPE_PRIVATE;
		}

		if ( $property->isProtected() ) {
			return self::SCOPE_PROTECTED;
		}

		return self::SCOPE_PUBLIC;
	}

	/**
	 * Processes methods.
	 *
	 * @param integer          $class_id Class ID.
	 * @param \ReflectionClass $class    Class.
	 *
	 * @return void
	 */
	protected function processClassMethods($class_id, \ReflectionClass $class)
	{
		$sql = 'SELECT Name, Id
				FROM ClassMethods
				WHERE ClassId = :class_id';
		$old_methods = $this->db->fetchPairs($sql, array(
			'class_id' => $class_id,
		));

		$insert_sql = '	INSERT INTO ClassMethods (ClassId, Name, ParameterCount, RequiredParameterCount, Scope, IsAbstract, IsFinal, IsStatic, IsVariadic, ReturnsReference, HasReturnType, ReturnType)
						VALUES (:class_id, :name, :parameter_count, :required_parameter_count, :scope, :is_abstract, :is_final, :is_static, :is_variadic, :returns_reference, :has_return_type, :return_type)';
		$update_sql = '	UPDATE ClassMethods
						SET	ParameterCount = :parameter_count,
							RequiredParameterCount = :required_parameter_count,
							Scope = :scope,
							IsAbstract = :is_abstract,
							IsFinal = :is_final,
							IsStatic = :is_static,
							IsVariadic = :is_variadic,
							ReturnsReference = :returns_reference,
							ReturnType = :return_type,
							HasReturnType = :has_return_type
						WHERE ClassId = :class_id AND Name = :name';

		$new_methods = array();
		$class_name = $class->getName();

		foreach ( $class->getMethods() as $method ) {
			if ( $method->class !== $class_name ) {
				continue;
			}

			$method_name = $method->getName();
			$new_methods[] = $method_name;

			// Doesn't work for parent classes (see https://github.com/goaop/parser-reflection/issues/16).
			$has_return_type = $method->hasReturnType();
			$return_type = $has_return_type ? (string)$method->getReturnType() : null;

			$this->db->perform(
				isset($old_methods[$method_name]) ? $update_sql : $insert_sql,
				array(
					'class_id' => $class_id,
					'name' => $method_name,
					'parameter_count' => $method->getNumberOfParameters(),
					'required_parameter_count' => $method->getNumberOfRequiredParameters(),
					'scope' => $this->getMethodScope($method),
					'is_abstract' => (int)$method->isAbstract(),
					'is_final' => (int)$method->isFinal(),
					'is_static' => (int)$method->isStatic(),
					'is_variadic' => (int)$method->isVariadic(),
					'returns_reference' => (int)$method->returnsReference(),
					'has_return_type' => (int)$has_return_type,
					'return_type' => $return_type,
				)
			);

			$method_id = isset($old_methods[$method_name]) ? $old_methods[$method_name] : $this->db->lastInsertId();
			$this->processClassMethodParameters($method_id, $method);
		}

		$delete_methods = array_diff(array_keys($old_methods), $new_methods);

		if ( $delete_methods ) {
			$this->deleteClassMethods($class_id, $delete_methods);
		}
	}

	/**
	 * Deletes methods.
	 *
	 * @param integer $class_id Class ID.
	 * @param array   $methods  Methods.
	 *
	 * @return void
	 */
	protected function deleteClassMethods($class_id, array $methods)
	{
		if ( $methods ) {
			// Delete only given methods.
			$sql = 'SELECT Id
					FROM ClassMethods
					WHERE ClassId = :class_id AND Name IN (:names)';
			$method_ids = $this->db->fetchCol($sql, array(
				'class_id' => $class_id,
				'names' => $methods,
			));
		}
		else {
			// Delete all methods in a class.
			$sql = 'SELECT Id
					FROM ClassMethods
					WHERE ClassId = :class_id';
			$method_ids = $this->db->fetchCol($sql, array(
				'class_id' => $class_id,
			));
		}

		// @codeCoverageIgnoreStart
		if ( !$method_ids ) {
			return;
		}
		// @codeCoverageIgnoreEnd

		$sql = 'DELETE FROM ClassMethods WHERE Id IN (:method_ids)';
		$this->db->perform($sql, array('method_ids' => $method_ids));

		$sql = 'DELETE FROM MethodParameters WHERE MethodId IN (:method_ids)';
		$this->db->perform($sql, array('method_ids' => $method_ids));
	}

	/**
	 * Processes method parameters.
	 *
	 * @param integer           $method_id Method ID.
	 * @param \ReflectionMethod $method    Method.
	 *
	 * @return void
	 */
	protected function processClassMethodParameters($method_id, \ReflectionMethod $method)
	{
		$sql = 'SELECT Name
				FROM MethodParameters
				WHERE MethodId = :method_id';
		$old_parameters = $this->db->fetchCol($sql, array(
			'method_id' => $method_id,
		));

		$insert_sql = '	INSERT INTO MethodParameters (MethodId, Name, Position, TypeClass, HasType, TypeName, AllowsNull, IsArray, IsCallable, IsOptional, IsVariadic, CanBePassedByValue, IsPassedByReference, HasDefaultValue, DefaultValue, DefaultConstant)
						VALUES (:method_id, :name, :position, :type_class, :has_type, :type_name, :allows_null, :is_array, :is_callable, :is_optional, :is_variadic, :can_be_passed_by_value, :is_passed_by_reference, :has_default_value, :default_value, :default_constant)';
		$update_sql = '	UPDATE MethodParameters
						SET	Position = :position,
							TypeClass = :type_class,
							HasType = :has_type,
							TypeName = :type_name,
							AllowsNull = :allows_null,
							IsArray = :is_array,
							IsCallable = :is_callable,
							IsOptional = :is_optional,
							IsVariadic = :is_variadic,
							CanBePassedByValue = :can_be_passed_by_value,
							IsPassedByReference = :is_passed_by_reference,
							HasDefaultValue = :has_default_value,
							DefaultValue = :default_value,
							DefaultConstant = :default_constant
						WHERE MethodId = :method_id AND Name = :name';

		$new_parameters = array();

		foreach ( $method->getParameters() as $position => $parameter ) {
			$parameter_name = $parameter->getName();
			$new_parameters[] = $parameter_name;

			$type_class = $parameter->getClass();
			$type_class = $type_class ? $type_class->getName() : null;

			// Doesn't work for parent classes (see https://github.com/goaop/parser-reflection/issues/16).
			$has_type = $parameter->hasType();
			$type_name = $has_type ? (string)$parameter->getType() : null;

			$has_default_value = $parameter->isDefaultValueAvailable();
			$default_value_is_constant = $has_default_value ? $parameter->isDefaultValueConstant() : false;

			$this->db->perform(
				in_array($parameter_name, $old_parameters) ? $update_sql : $insert_sql,
				array(
					'method_id' => $method_id,
					'name' => $parameter_name,
					'position' => $position,
					'type_class' => $type_class,
					'has_type' => (int)$has_type,
					'type_name' => $type_name,
					'allows_null' => (int)$parameter->allowsNull(),
					'is_array' => (int)$parameter->isArray(),
					'is_callable' => (int)$parameter->isCallable(),
					'is_optional' => (int)$parameter->isOptional(),
					'is_variadic' => (int)$parameter->isVariadic(),
					'can_be_passed_by_value' => (int)$parameter->canBePassedByValue(),
					'is_passed_by_reference' => (int)$parameter->isPassedByReference(),
					'has_default_value' => (int)$has_default_value,
					'default_value' => $has_default_value ? json_encode($parameter->getDefaultValue()) : null,
					'default_constant' => $default_value_is_constant ? $parameter->getDefaultValueConstantName() : null,
				)
			);
		}

		$delete_parameters = array_diff($old_parameters, $new_parameters);

		if ( $delete_parameters ) {
			$sql = 'DELETE FROM MethodParameters
					WHERE MethodId = :method_id AND Name IN (:names)';
			$this->db->perform($sql, array(
				'method_id' => $method_id,
				'names' => $delete_parameters,
			));
		}
	}

	/**
	 * Returns method scope.
	 *
	 * @param \ReflectionMethod $method Method.
	 *
	 * @return integer
	 */
	protected function getMethodScope(\ReflectionMethod $method)
	{
		if ( $method->isPrivate() ) {
			return self::SCOPE_PRIVATE;
		}

		if ( $method->isProtected() ) {
			return self::SCOPE_PROTECTED;
		}

		return self::SCOPE_PUBLIC;
	}

	/**
	 * Processes raw relations for all classes.
	 *
	 * @param KnowledgeBase $knowledge_base Knowledge base.
	 *
	 * @return void
	 */
	protected function processClassRawRelations(KnowledgeBase $knowledge_base)
	{
		$sql = 'SELECT Id, RawRelations
				FROM Classes
				WHERE RawRelations IS NOT NULL';
		$raw_relations = $this->db->yieldPairs($sql);

		foreach ( $raw_relations as $class_id => $class_raw_relations ) {
			$sql = 'SELECT RelatedClass
					FROM ClassRelations
					WHERE ClassId = :class_id';
			$old_class_relations = $this->db->fetchCol($sql, array(
				'class_id' => $class_id,
			));

			$new_class_relations = array();

			foreach ( json_decode($class_raw_relations, true) as $class_raw_relation ) {
				list ($related_class, $relation_type, $is_internal) = $class_raw_relation;

				$new_class_relations[] = $this->addRelation(
					$knowledge_base,
					$class_id,
					$related_class,
					$relation_type,
					$is_internal,
					$old_class_relations
				);
			}

			$delete_class_relations = array_diff($old_class_relations, $new_class_relations);

			if ( $delete_class_relations ) {
				$sql = 'DELETE FROM ClassRelations
						WHERE ClassId = :class_id AND RelatedClass IN (:related_classes)';
				$this->db->perform($sql, array(
					'class_id' => $class_id,
					'related_classes' => $delete_class_relations,
				));
			}
		}

		$sql = 'UPDATE Classes
				SET RawRelations = NULL';
		$this->db->perform($sql);
	}

	/**
	 * Adds a relation.
	 *
	 * @param KnowledgeBase $knowledge_base Knowledge base.
	 * @param integer       $class_id       Class ID.
	 * @param string        $related_class  Related class.
	 * @param integer       $relation_type  Relation type.
	 * @param boolean       $is_internal    Is internal.
	 * @param array         $old_relations  Old relations.
	 *
	 * @return string
	 */
	protected function addRelation(
		KnowledgeBase $knowledge_base,
		$class_id,
		$related_class,
		$relation_type,
		$is_internal,
		array $old_relations
	) {
		$insert_sql = '	INSERT INTO ClassRelations (ClassId, RelatedClass, RelatedClassId, RelationType)
						VALUES (:class_id, :related_class, :related_class_id, :relation_type)';
		$update_sql = ' UPDATE ClassRelations
						SET RelationType = :relation_type
						WHERE ClassId = :class_id AND RelatedClassId = :related_class_id';

		if ( $is_internal ) {
			$related_class_id = 0;
		}
		else {
			$related_class_file = realpath(ReflectionEngine::locateClassFile($related_class));

			$sql = 'SELECT Id
					FROM Classes
					WHERE FileId = :file_id AND Name = :name';
			$related_class_id = $this->db->fetchValue($sql, array(
				'file_id' => $knowledge_base->processFile($related_class_file),
				'name' => $related_class,
			));
		}

		$this->db->perform(
			in_array($related_class, $old_relations) ? $update_sql : $insert_sql,
			array(
				'class_id' => $class_id,
				'related_class' => $related_class,
				'related_class_id' => $related_class_id,
				'relation_type' => $relation_type,
			)
		);

		return $related_class;
	}

}
