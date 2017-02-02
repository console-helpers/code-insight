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


class ReporterFactory
{

	/**
	 * Backwards compatibility reporters.
	 *
	 * @var AbstractReporter[]
	 */
	protected $reporters = array();

	/**
	 * Adds backwards compatibility reporter.
	 *
	 * @param AbstractReporter $bc_reporter Backwards reporter reporter.
	 *
	 * @return void
	 * @throws \LogicException When backwards compatibility reporter is already added.
	 */
	public function add(AbstractReporter $bc_reporter)
	{
		$name = $bc_reporter->getName();

		if ( array_key_exists($name, $this->reporters) ) {
			throw new \LogicException(
				'The backwards compatibility reporter with "' . $name . '" name is already added.'
			);
		}

		$this->reporters[$name] = $bc_reporter;
	}

	/**
	 * Gets backwards compatibility reporter by name.
	 *
	 * @param string $name Backwards compatibility reporter name.
	 *
	 * @return AbstractReporter
	 * @throws \LogicException When Backwards compatibility reporter wasn't found.
	 */
	public function get($name)
	{
		if ( !array_key_exists($name, $this->reporters) ) {
			throw new \LogicException(
				'The backwards compatibility reporter with "' . $name . '" name is not found.'
			);
		}

		return $this->reporters[$name];
	}

	/**
	 * Returns possible reporters.
	 *
	 * @return array
	 */
	public function getNames()
	{
		return array_keys($this->reporters);
	}

}
