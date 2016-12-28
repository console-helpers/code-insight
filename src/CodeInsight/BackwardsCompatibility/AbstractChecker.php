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
use Doctrine\Common\Cache\CacheProvider;

abstract class AbstractChecker
{

	/**
	 * Source database.
	 *
	 * @var ExtendedPdoInterface
	 */
	protected $sourceDatabase;

	/**
	 * Target database.
	 *
	 * @var ExtendedPdoInterface
	 */
	protected $targetDatabase;

	/**
	 * Cache.
	 *
	 * @var CacheProvider
	 */
	protected $cache;

	/**
	 * Incidents.
	 *
	 * @var array
	 */
	private $_incidents = array();

	/**
	 * AbstractChecker constructor.
	 *
	 * @param CacheProvider $cache Cache provider.
	 */
	public function __construct(CacheProvider $cache)
	{
		$this->cache = $cache;
	}

	/**
	 * Returns backwards compatibility checker name.
	 *
	 * @return string
	 */
	abstract public function getName();

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

		$this->doCheck();

		return array_filter($this->_incidents);
	}

	/**
	 * Collects backwards compatibility violations.
	 *
	 * @return void
	 */
	abstract protected function doCheck();

	/**
	 * Builds string representation of a parameter.
	 *
	 * @param array $parameter_data Parameter data.
	 *
	 * @return string
	 */
	protected function paramToString(array $parameter_data)
	{
		if ( $parameter_data['HasType'] ) {
			$type = $parameter_data['TypeName'];
		}
		elseif ( $parameter_data['IsArray'] ) {
			$type = 'array';
		}
		elseif ( $parameter_data['IsCallable'] ) {
			$type = 'callable';
		}
		else {
			$type = $parameter_data['TypeClass'];
		}

		$hash_part = strlen($type) ? $type . ' ' : '';

		if ( $parameter_data['IsPassedByReference'] ) {
			$hash_part .= '&$' . $parameter_data['Name'];
		}
		else {
			$hash_part .= '$' . $parameter_data['Name'];
		}

		if ( $parameter_data['HasDefaultValue'] ) {
			$hash_part .= ' = ';

			if ( $parameter_data['DefaultConstant'] ) {
				$hash_part .= $parameter_data['DefaultConstant'];
			}
			else {
				$hash_part .= $this->decodeValue($parameter_data['DefaultValue']);
			}
		}

		return $hash_part;
	}

	/**
	 * Decodes json-encoded PHP value.
	 *
	 * @param string $json_string JSON string.
	 *
	 * @return string
	 */
	protected function decodeValue($json_string)
	{
		$value = var_export(json_decode($json_string), true);
		$value = str_replace(array("\t", "\n"), '', $value);
		$value = str_replace('array (', 'array(', $value);

		return $value;
	}

	/**
	 * Defines incident groups.
	 *
	 * @param array $groups Groups.
	 *
	 * @return void
	 */
	protected function defineIncidentGroups(array $groups)
	{
		$this->_incidents = array();

		foreach ( $groups as $group ) {
			$this->_incidents[$group] = array();
		}
	}

	/**
	 * Adds incident.
	 *
	 * @param string      $group     Incident group.
	 * @param string      $incident  Incident description.
	 * @param string|null $old_value Old value.
	 * @param string|null $new_value New value.
	 *
	 * @return void
	 */
	protected function addIncident($group, $incident, $old_value = null, $new_value = null)
	{
		if ( isset($old_value) || isset($new_value) ) {
			$incident = '<fg=white;options=bold>' . $incident . '</>' . PHP_EOL;
			$incident .= 'OLD: ' . $old_value . PHP_EOL;
			$incident .= 'NEW: ' . $new_value . PHP_EOL;
		}

		$this->_incidents[$group][] = $incident;
	}

	/**
	 * Returns cache key valid for specific database only.
	 *
	 * @param ExtendedPdoInterface $db        Database.
	 * @param string               $cache_key Cache key.
	 *
	 * @return string
	 */
	protected function getCacheKey(ExtendedPdoInterface $db, $cache_key)
	{
		return sha1($db->getDsn()) . ':' . $cache_key;
	}

}
