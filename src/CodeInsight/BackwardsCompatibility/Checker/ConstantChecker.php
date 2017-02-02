<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\BackwardsCompatibility\Checker;


class ConstantChecker extends AbstractChecker
{

	const TYPE_CONSTANT_DELETED = 'constant.deleted';

	/**
	 * Returns backwards compatibility checker name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'constant';
	}

	/**
	 * Collects backwards compatibility violations.
	 *
	 * @return void
	 */
	protected function doCheck()
	{
		$sql = 'SELECT Name
				FROM Constants';
		$source_constants = $this->sourceDatabase->fetchCol($sql);
		$target_constants = $this->targetDatabase->fetchCol($sql);

		foreach ( $source_constants as $source_constant_name ) {
			if ( !in_array($source_constant_name, $target_constants) ) {
				$this->addIncident(self::TYPE_CONSTANT_DELETED, $source_constant_name);
				continue;
			}
		}
	}

}
