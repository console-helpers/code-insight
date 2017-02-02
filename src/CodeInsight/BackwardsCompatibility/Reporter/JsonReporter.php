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


use Camspiers\JsonPretty\JsonPretty;

class JsonReporter extends AbstractReporter
{

	/**
	 * Returns reporter name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'json';
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
		$json_pretty = new JsonPretty();

		return $json_pretty->prettify($bc_breaks);
	}

}
