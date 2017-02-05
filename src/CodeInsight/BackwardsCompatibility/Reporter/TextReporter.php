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


class TextReporter extends AbstractReporter
{

	/**
	 * Returns reporter name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'text';
	}

	/**
	 * Generates report.
	 *
	 * @param array $bc_breaks BC breaks.
	 *
	 * @return string
	 */
	public function generate(array $bc_breaks)
	{
		if ( !$bc_breaks ) {
			return 'No backwards compatibility breaks detected.';
		}

		$ret = 'Backward compatibility breaks:' . PHP_EOL;

		foreach ( $this->groupByType($bc_breaks) as $bc_break => $incidents ) {
			$ret .= PHP_EOL;

			$bc_break = ucwords(str_replace(array('.', '_'), ' ', $bc_break));
			$ret .= '<fg=red>=== ' . $bc_break . ' (' . count($incidents) . ') ===</>' . PHP_EOL;

			foreach ( $this->sortByElement($incidents) as $incident_data ) {
				if ( array_key_exists('old', $incident_data) ) {
					$ret .= ' * <fg=white;options=bold>' . $incident_data['element'] . '</>' . PHP_EOL;
					$ret .= '   OLD: ' . $incident_data['old'] . PHP_EOL;
					$ret .= '   NEW: ' . $incident_data['new'] . PHP_EOL;
					$ret .= PHP_EOL;
				}
				else {
					$ret .= ' * ' . $incident_data['element'] . PHP_EOL;
				}
			}
		}

		return $ret;
	}

}
