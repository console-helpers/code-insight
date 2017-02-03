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

class FunctionChecker extends AbstractChecker
{

	const TYPE_FUNCTION_DELETED = 'function.deleted';
	const TYPE_FUNCTION_SIGNATURE_CHANGED = 'function.signature_changed';

	/**
	 * Source function data.
	 *
	 * @var array
	 */
	protected $sourceFunctionData = array();

	/**
	 * Target function data.
	 *
	 * @var array
	 */
	protected $targetFunctionData = array();

	/**
	 * Returns backwards compatibility checker name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'function';
	}

	/**
	 * Collects backwards compatibility violations.
	 *
	 * @return void
	 */
	protected function doCheck()
	{
		$sql = 'SELECT Name, Id
				FROM Functions';
		$source_functions = $this->sourceDatabase->fetchAssoc($sql);
		$target_functions = $this->targetDatabase->fetchAssoc($sql);

		foreach ( $source_functions as $source_function_name => $source_function_data ) {
			if ( !isset($target_functions[$source_function_name]) ) {
				$this->addIncident(self::TYPE_FUNCTION_DELETED, $source_function_name);
				continue;
			}

			$this->sourceFunctionData = $source_function_data;
			$this->sourceFunctionData['ParameterSignature'] = $this->getFunctionParameterSignature(
				$this->sourceDatabase,
				$this->sourceFunctionData['Id']
			);

			$this->targetFunctionData = $target_functions[$source_function_name];
			$this->targetFunctionData['ParameterSignature'] = $this->getFunctionParameterSignature(
				$this->targetDatabase,
				$this->targetFunctionData['Id']
			);

			$this->processFunction();
		}
	}

	/**
	 * Calculates function parameter signature.
	 *
	 * @param ExtendedPdoInterface $db          Database.
	 * @param integer              $function_id Function ID.
	 *
	 * @return integer
	 */
	protected function getFunctionParameterSignature(ExtendedPdoInterface $db, $function_id)
	{
		$sql = 'SELECT *
				FROM FunctionParameters
				WHERE FunctionId = :function_id
				ORDER BY Position ASC';
		$function_parameters = $db->fetchAll($sql, array('function_id' => $function_id));

		$hash_parts = array();

		foreach ( $function_parameters as $function_parameter_data ) {
			$hash_parts[] = $this->paramToString($function_parameter_data);
		}

		return implode(', ', $hash_parts);
	}

	/**
	 * Processes function.
	 *
	 * @return void
	 */
	protected function processFunction()
	{
		$function_name = $this->sourceFunctionData['Name'];

		$source_signature = $this->sourceFunctionData['ParameterSignature'];
		$target_signature = $this->targetFunctionData['ParameterSignature'];

		if ( !$this->isParamSignatureCompatible($source_signature, $target_signature) ) {
			$this->addIncident(
				self::TYPE_FUNCTION_SIGNATURE_CHANGED,
				$function_name,
				$source_signature,
				$target_signature
			);
		}
	}

}
