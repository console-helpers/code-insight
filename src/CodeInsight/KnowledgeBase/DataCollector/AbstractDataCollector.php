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


use Aura\Sql\ExtendedPdoInterface;
use ConsoleHelpers\CodeInsight\KnowledgeBase\KnowledgeBase;
use Go\ParserReflection\ReflectionFileNamespace;

abstract class AbstractDataCollector
{

	/**
	 * Database.
	 *
	 * @var ExtendedPdoInterface
	 */
	protected $db;

	/**
	 * Creates data collector instance.
	 *
	 * @param ExtendedPdoInterface $db Database.
	 */
	public function __construct(ExtendedPdoInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * Collect data from a namespace.
	 *
	 * @param integer                 $file_id   File id.
	 * @param ReflectionFileNamespace $namespace Namespace.
	 *
	 * @return void
	 */
	abstract public function collectData($file_id, ReflectionFileNamespace $namespace);

	/**
	 * Aggregate previously collected data.
	 *
	 * @param KnowledgeBase $knowledge_base Knowledge base.
	 *
	 * @return void
	 */
	public function aggregateData(KnowledgeBase $knowledge_base)
	{

	}

	/**
	 * Delete previously collected data for a files.
	 *
	 * @param array $file_ids File IDs.
	 *
	 * @return void
	 */
	abstract public function deleteData(array $file_ids);

	/**
	 * Returns statistics about the code.
	 *
	 * @return array
	 */
	public function getStatistics()
	{
		return array();
	}

	/**
	 * Finds backward compatibility breaks.
	 *
	 * @param ExtendedPdoInterface $source_db Source database.
	 *
	 * @return array
	 */
	public function getBackwardsCompatibilityBreaks(ExtendedPdoInterface $source_db)
	{
		return array();
	}

}
