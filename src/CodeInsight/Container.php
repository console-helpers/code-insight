<?php
namespace ConsoleHelpers\CodeInsight;


class Container extends \ConsoleHelpers\ConsoleKit\Container
{

	/**
	 * {@inheritdoc}
	 */
	public function __construct(array $values = array())
	{
		parent::__construct($values);

		$this['app_name'] = 'Code-Insight';
		$this['app_version'] = '@git-version@';

		$this['working_directory_sub_folder'] = '.code-insight';

		$this['config_defaults'] = array();
	}

}
