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


class CheckerFactory
{

	/**
	 * Backwards compatibility checkers.
	 *
	 * @var AbstractChecker[]
	 */
	protected $checkers = array();

	/**
	 * Adds backwards compatibility checker.
	 *
	 * @param AbstractChecker $bc_checker Backwards compatibility checker.
	 *
	 * @return void
	 * @throws \LogicException When backwards compatibility checker is already added.
	 */
	public function add(AbstractChecker $bc_checker)
	{
		$name = $bc_checker->getName();

		if ( array_key_exists($name, $this->checkers) ) {
			throw new \LogicException(
				'The backwards compatibility checker with "' . $name . '" name is already added.'
			);
		}

		$this->checkers[$name] = $bc_checker;
	}

	/**
	 * Gets backwards compatibility checker by name.
	 *
	 * @param string $name Backwards compatibility checker name.
	 *
	 * @return AbstractChecker
	 * @throws \LogicException When Backwards compatibility checker wasn't found.
	 */
	public function get($name)
	{
		if ( !array_key_exists($name, $this->checkers) ) {
			throw new \LogicException(
				'The backwards compatibility checker with "' . $name . '" name is not found.'
			);
		}

		return $this->checkers[$name];
	}

}
