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


use Go\ParserReflection\ReflectionFileNamespace;

class FunctionDataCollector extends AbstractDataCollector
{

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
		$sql = 'SELECT Name, Id
				FROM Functions
				WHERE FileId = :file_id';
		$old_functions = $this->db->fetchPairs($sql, array(
			'file_id' => $file_id,
		));

		$insert_sql = '	INSERT INTO Functions (FileId, Name, ParameterCount, RequiredParameterCount, IsVariadic, ReturnsReference, HasReturnType, ReturnType)
						VALUES (:file_id, :name, :parameter_count, :required_parameter_count, :is_variadic, :returns_reference, :has_return_type, :return_type)';
		$update_sql = '	UPDATE Functions
						SET	ParameterCount = :parameter_count,
							RequiredParameterCount = :required_parameter_count,
							IsVariadic = :is_variadic,
							ReturnsReference = :returns_reference,
							ReturnType = :return_type,
							HasReturnType = :has_return_type
						WHERE FileId = :file_id AND Name = :name';

		$new_functions = array();

		foreach ( $namespace->getFunctions() as $function ) {
			$function_name = $function->getName();
			$new_functions[] = $function_name;

			$has_return_type = $function->hasReturnType();
			$return_type = $has_return_type ? (string)$function->getReturnType() : null;

			$this->db->perform(
				isset($old_functions[$function_name]) ? $update_sql : $insert_sql,
				array(
					'file_id' => $file_id,
					'name' => $function_name,
					'parameter_count' => $function->getNumberOfParameters(),
					'required_parameter_count' => $function->getNumberOfRequiredParameters(),
					'is_variadic' => (int)$function->isVariadic(),
					'returns_reference' => (int)$function->returnsReference(),
					'has_return_type' => (int)$has_return_type,
					'return_type' => $return_type,
				)
			);

			$function_id = isset($old_functions[$function_name]) ? $old_functions[$function_name] : $this->db->lastInsertId();
			$this->processFunctionParameters($function_id, $function);
		}

		$delete_functions = array_diff(array_keys($old_functions), $new_functions);

		if ( $delete_functions ) {
			$this->deleteFunctions($file_id, $delete_functions);
		}
	}

	/**
	 * Deletes functions.
	 *
	 * @param integer $file_id   File ID.
	 * @param array   $functions Methods.
	 *
	 * @return void
	 */
	protected function deleteFunctions($file_id, array $functions)
	{
		if ( $functions ) {
			// Delete only given functions.
			$sql = 'SELECT Id
					FROM Functions
					WHERE FileId = :file_id AND Name IN (:names)';
			$function_ids = $this->db->fetchCol($sql, array(
				'file_id' => $file_id,
				'names' => $functions,
			));
		}
		else {
			// Delete all functions in a file.
			$sql = 'SELECT Id
					FROM Functions
					WHERE FileId = :file_id';
			$function_ids = $this->db->fetchCol($sql, array(
				'file_id' => $file_id,
			));
		}

		// @codeCoverageIgnoreStart
		if ( !$function_ids ) {
			return;
		}
		// @codeCoverageIgnoreEnd

		$sql = 'DELETE FROM Functions WHERE Id IN (:function_ids)';
		$this->db->perform($sql, array('function_ids' => $function_ids));

		$sql = 'DELETE FROM FunctionParameters WHERE FunctionId IN (:function_ids)';
		$this->db->perform($sql, array('function_ids' => $function_ids));
	}

	/**
	 * Processes function parameters.
	 *
	 * @param integer             $function_id Function ID.
	 * @param \ReflectionFunction $function    Function.
	 *
	 * @return void
	 */
	protected function processFunctionParameters($function_id, \ReflectionFunction $function)
	{
		$sql = 'SELECT Name
				FROM FunctionParameters
				WHERE FunctionId = :function_id';
		$old_parameters = $this->db->fetchCol($sql, array(
			'function_id' => $function_id,
		));

		$insert_sql = '	INSERT INTO FunctionParameters (FunctionId, Name, Position, TypeClass, HasType, TypeName, AllowsNull, IsArray, IsCallable, IsOptional, IsVariadic, CanBePassedByValue, IsPassedByReference, HasDefaultValue, DefaultValue, DefaultConstant)
						VALUES (:function_id, :name, :position, :type_class, :has_type, :type_name, :allows_null, :is_array, :is_callable, :is_optional, :is_variadic, :can_be_passed_by_value, :is_passed_by_reference, :has_default_value, :default_value, :default_constant)';
		$update_sql = '	UPDATE FunctionParameters
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
						WHERE FunctionId = :function_id AND Name = :name';

		$new_parameters = array();

		foreach ( $function->getParameters() as $position => $parameter ) {
			$parameter_name = $parameter->getName();
			$new_parameters[] = $parameter_name;

			$type_class = $parameter->getClass();
			$type_class = $type_class ? $type_class->getName() : null;

			$has_type = $parameter->hasType();
			$type_name = $has_type ? (string)$parameter->getType() : null;

			$has_default_value = $parameter->isDefaultValueAvailable();
			$default_value_is_constant = $has_default_value ? $parameter->isDefaultValueConstant() : false;

			$this->db->perform(
				in_array($parameter_name, $old_parameters) ? $update_sql : $insert_sql,
				array(
					'function_id' => $function_id,
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
			$sql = 'DELETE FROM FunctionParameters
					WHERE FunctionId = :function_id AND Name IN (:names)';
			$this->db->perform($sql, array(
				'function_id' => $function_id,
				'names' => $delete_parameters,
			));
		}
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
		foreach ( $file_ids as $file_id ) {
			$this->deleteFunctions($file_id, array());
		}
	}

	/**
	 * Returns statistics about the code.
	 *
	 * @return array
	 */
	public function getStatistics()
	{
		$sql = 'SELECT COUNT(*)
				FROM Functions';
		$function_count = $this->db->fetchValue($sql);

		return array(
			'Functions' => $function_count,
		);
	}

}
