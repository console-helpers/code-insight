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


class InPortalClassChecker extends ClassChecker
{

	/**
	 * Methods for which scope change isn't a BC break.
	 *
	 * @var array
	 */
	protected $ignoreScopeChangeMethods = array('mapPermissions', 'SetCustomQuery');

	/**
	 * Returns backwards compatibility checker name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'inportal_class';
	}

	/**
	 * Processes method.
	 *
	 * @return void
	 */
	protected function processMethod()
	{
		if ( $this->isEventHandler() && ($this->isEvent() || $this->ignoreScopeChange()) ) {
			$this->targetMethodData['Scope'] = $this->sourceMethodData['Scope'];
		}

		if ( $this->isTagProcessor() && ($this->sourceMethodData['ParameterSignature'] === 'array $params') ) {
			$this->targetMethodData['Scope'] = $this->sourceMethodData['Scope'];
		}

		parent::processMethod();
	}

	/**
	 * Builds string representation of a parameter.
	 *
	 * @param array $parameter_data Parameter data.
	 *
	 * @return string
	 */
	protected function paramToString(array $parameter_data)
	{
		$hash_part = parent::paramToString($parameter_data);

		if ( $this->isEventHandler() ) {
			$hash_part = str_replace('&$event', '$event', $hash_part);
			$hash_part = str_replace('\kEvent $event', '$event', $hash_part);
			$hash_part = str_replace('kEvent $event', '$event', $hash_part);
		}
		elseif ( $this->isTagProcessor() ) {
			$hash_part = str_replace('array $params', '$params', $hash_part);
			$hash_part = str_replace('$params', 'array $params', $hash_part);
		}

		return $hash_part;
	}

	/**
	 * Determines if current class is an event handler.
	 *
	 * @return boolean
	 */
	protected function isEventHandler()
	{
		$class_name = $this->sourceClassData['Name'];

		return substr($class_name, -12) === 'EventHandler' || $class_name === 'AdminEventsHandler';
	}

	/**
	 * Determines if current class is a tag processor.
	 *
	 * @return boolean
	 */
	protected function isTagProcessor()
	{
		return substr($this->sourceClassData['Name'], -12) === 'TagProcessor';
	}

	/**
	 * Determines if current method is an event.
	 *
	 * @return boolean
	 */
	protected function isEvent()
	{
		return substr($this->sourceMethodData['Name'], 0, 2) === 'On';
	}

	/**
	 * Determines if method scope change should be ignored.
	 *
	 * @return boolean
	 */
	protected function ignoreScopeChange()
	{
		return in_array($this->sourceMethodData['Name'], $this->ignoreScopeChangeMethods);
	}

}
