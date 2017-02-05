<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\BackwardsCompatibility\Reporter;


abstract class AbstractReporter
{

	/**
	 * Returns reporter name.
	 *
	 * @return string
	 */
	abstract public function getName();

	/**
	 * Generates report.
	 *
	 * @param array $bc_breaks BC breaks.
	 *
	 * @return string
	 */
	abstract public function generate(array $bc_breaks);

	/**
	 * Groups BC breaks by type.
	 *
	 * @param array $bc_breaks BC breaks.
	 *
	 * @return array
	 */
	protected function groupByType(array $bc_breaks)
	{
		$ret = array();

		foreach ( $bc_breaks as $bc_break_data ) {
			$type = $bc_break_data['type'];

			if ( !isset($ret[$type]) ) {
				$ret[$type] = array();
			}

			$ret[$type][] = $bc_break_data;
		}

		return $ret;
	}

	/**
	 * Sorts BC breaks by element.
	 *
	 * @param array $bc_breaks BC breaks.
	 *
	 * @return array
	 */
	protected function sortByElement(array $bc_breaks)
	{
		usort($bc_breaks, array($this, 'sortByElementCallback'));

		return $bc_breaks;
	}

	/**
	 * Sorts BC breaks by element.
	 *
	 * @param array $incident_a Incident A.
	 * @param array $incident_b Incident B.
	 *
	 * @return integer
	 */
	public function sortByElementCallback(array $incident_a, array $incident_b)
	{
		return strcmp($incident_a['element'], $incident_b['element']);
	}

}
