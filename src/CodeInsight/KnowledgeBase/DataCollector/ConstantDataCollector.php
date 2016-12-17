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

class ConstantDataCollector extends AbstractDataCollector
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
		$constants = $namespace->getConstants(true);

		$sql = 'SELECT Name
				FROM Constants
				WHERE FileId = :file_id';
		$old_constants = $this->db->fetchCol($sql, array(
			'file_id' => $file_id,
		));

		$insert_sql = 'INSERT INTO Constants (FileId, Name, Value) VALUES (:file_id, :name, :value)';
		$update_sql = 'UPDATE Constants SET Value = :value WHERE FileId = :file_id AND Name = :name';

		foreach ( $constants as $constant_name => $constant_value ) {
			$this->db->perform(
				in_array($constant_name, $old_constants) ? $update_sql : $insert_sql,
				array(
					'file_id' => $file_id,
					'name' => $constant_name,
					'value' => json_encode($constant_value),
				)
			);
		}

		$delete_constants = array_diff($old_constants, array_keys($constants));

		if ( $delete_constants ) {
			$sql = 'DELETE FROM Constants
					WHERE FileId = :file_id AND Name IN (:names)';
			$this->db->perform($sql, array(
				'file_id' => $file_id,
				'names' => $delete_constants,
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
		$sql = 'DELETE FROM Constants
				WHERE FileId IN (:file_ids)';
		$this->db->perform($sql, array(
			'file_ids' => $file_ids,
		));
	}

	/**
	 * Returns statistics about the code.
	 *
	 * @return array
	 */
	public function getStatistics()
	{
		$sql = 'SELECT COUNT(*)
				FROM Constants';
		$constant_count = $this->db->fetchValue($sql);

		return array(
			'Constants' => $constant_count,
		);
	}

}
