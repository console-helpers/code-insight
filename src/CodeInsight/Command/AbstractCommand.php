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
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;

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
	 * Returns input from completion context.
	 *
	 * @param CompletionContext $context Completion context.
	 *
	 * @return InputInterface
	 */
	protected function getInputFromCompletionContext(CompletionContext $context)
	{
		$words = $context->getWords();
		array_splice($words, 1, 1); // Remove the command name.

		return new ArgvInput($words, $this->getDefinition());
	}

	/**
	 * Returns and validates path.
	 *
	 * @param string $raw_path Raw path.
	 *
	 * @return string
	 * @throws \InvalidArgumentException When path isn't valid.
	 */
	protected function getPath($raw_path)
	{
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
