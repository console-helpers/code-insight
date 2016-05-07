<?php
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

}
