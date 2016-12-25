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


use Stecman\Component\Symfony\Console\BashCompletion\Completion;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\ShellPathCompletion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand as SymfonyCompletionCommand;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;

class CompletionCommand extends SymfonyCompletionCommand
{

	/**
	 * Configure the CompletionHandler instance before it is run
	 *
	 * @param CompletionHandler $handler Completion handler.
	 *
	 * @return void
	 */
	protected function configureCompletion(CompletionHandler $handler)
	{
		$handler->addHandler(new ShellPathCompletion(
			'report:create',
		    'project-path',
		    Completion::TYPE_ARGUMENT
		));
	}

}
