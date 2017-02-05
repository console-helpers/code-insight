<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\Command;


use ConsoleHelpers\ConsoleKit\Command\AbstractCommand as BaseCommand;

/**
 * Base command class.
 */
abstract class AbstractCommand extends BaseCommand
{

	/**
	 * Prepare dependencies.
	 *
	 * @return void
	 */
	protected function prepareDependencies()
	{
		$container = $this->getContainer();
	}

	/**
	 * Returns and validates path.
	 *
	 * @param string $argument_name Argument name, that contains path.
	 *
	 * @return string
	 * @throws \InvalidArgumentException When path isn't valid.
	 */
	protected function getPath($argument_name)
	{
		$raw_path = $this->io->getArgument($argument_name);

		if ( !$raw_path ) {
			return '';
		}

		$path = realpath($raw_path);

		if ( !$path || !file_exists($path) || !is_dir($path) ) {
			throw new \InvalidArgumentException('The "' . $raw_path . '" path is invalid.');
		}

		return $path;
	}

}
