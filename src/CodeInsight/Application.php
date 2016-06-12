<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight;


use ConsoleHelpers\CodeInsight\Command\CompletionCommand;
use ConsoleHelpers\CodeInsight\Command\ReportCommand;
use ConsoleHelpers\CodeInsight\Command\Dev\MigrationCreateCommand;
use ConsoleHelpers\ConsoleKit\Application as BaseApplication;
use ConsoleHelpers\CodeInsight\Command\MissingTestsCommand;
use Symfony\Component\Console\Command\Command;

class Application extends BaseApplication
{

	/**
	 * Initializes all the composer commands.
	 *
	 * @return Command[] An array of default Command instances.
	 */
	protected function getDefaultCommands()
	{
		$default_commands = parent::getDefaultCommands();
		$default_commands[] = new MissingTestsCommand();
		$default_commands[] = new ReportCommand();
		$default_commands[] = new CompletionCommand();

		if ( !$this->isPharFile() ) {
			$default_commands[] = new MigrationCreateCommand();
		}

		return $default_commands;
	}

	/**
	 * Detects, when we're inside PHAR file.
	 *
	 * @return boolean
	 */
	protected function isPharFile()
	{
		return strpos(__DIR__, 'phar://') === 0;
	}

}
