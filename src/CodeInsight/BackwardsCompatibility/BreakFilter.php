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


class BreakFilter
{

	/**
	 * Removes ignored BC breaks.
	 *
	 * @param array $bc_breaks BC breaks.
	 * @param array $rules     Rules.
	 *
	 * @return array
	 */
	public function removeMatching(array $bc_breaks, array $rules)
	{
		$ret = array();

		foreach ( $bc_breaks as $break_data ) {
			if ( !$this->isMatching($break_data, $rules) ) {
				$ret[] = $break_data;
			}
		}

		return $ret;
	}

	/**
	 * Determines if BC break is ignored.
	 *
	 * @param array $break_data Break data.
	 * @param array $rules      Rules.
	 *
	 * @return boolean
	 */
	protected function isMatching(array $break_data, array $rules)
	{
		if ( !$rules ) {
			return false;
		}

		foreach ( $rules as $rule_data ) {
			if ( isset($rule_data['type']) && $rule_data['type'] !== $break_data['type'] ) {
				continue;
			}

			if ( isset($rule_data['element']) && $rule_data['element'] !== $break_data['element'] ) {
				continue;
			}

			if ( isset($rule_data['old']) ) {
				if ( !isset($break_data['old']) || $rule_data['old'] !== $break_data['old'] ) {
					continue;
				}
			}

			if ( isset($rule_data['new']) ) {
				if ( !isset($break_data['new']) || $rule_data['new'] !== $break_data['new'] ) {
					continue;
				}
			}

			return true;
		}

		return false;
	}

}
