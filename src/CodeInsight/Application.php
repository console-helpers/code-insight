<?php
namespace ConsoleHelpers\CodeInsight;


use ConsoleHelpers\ConsoleKit\Application as BaseApplication;
use ConsoleHelpers\CodeInsight\Command\TestsCommand;
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
		$default_commands[] = new TestsCommand();

		return $default_commands;
	}

}
