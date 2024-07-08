<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace Tests\ConsoleHelpers\CodeInsight\BackwardsCompatibility;


use ConsoleHelpers\CodeInsight\BackwardsCompatibility\BreakFilter;
use Tests\ConsoleHelpers\ConsoleKit\AbstractTestCase;

class BreakFilterTest extends AbstractTestCase
{

	/**
	 * Break filter.
	 *
	 * @var BreakFilter
	 */
	protected $breakFilter;

    /**
     * @before
     */
	public function setUpTest()
	{
		$this->breakFilter = new BreakFilter();
	}

	/**
	 * @dataProvider removeIgnoredDataProvider
	 */
	public function testRemoveIgnored(array $ignore_rules, array $result)
	{
		$bc_breaks = array(
			array('type' => 't1', 'element' => 'e1'),
			array('type' => 't1', 'element' => 'e1', 'old' => 'o1', 'new' => 'n1'),
			array('type' => 't1', 'element' => 'e2', 'old' => 'o2', 'new' => 'n2'),
			array('type' => 't2', 'element' => 'e2'),
			array('type' => 't2', 'element' => 'e1', 'old' => 'o1', 'new' => 'n1'),
		);

		$this->assertEquals($result, $this->breakFilter->removeMatching($bc_breaks, $ignore_rules));
	}

	public static function removeIgnoredDataProvider()
	{
		return array(
			'type only' => array(
				array(
					array('type' => 't1'),
				),
				array(
					array('type' => 't2', 'element' => 'e2'),
					array('type' => 't2', 'element' => 'e1', 'old' => 'o1', 'new' => 'n1'),
				),
			),
			'element only' => array(
				array(
					array('element' => 'e1'),
				),
				array(
					array('type' => 't1', 'element' => 'e2', 'old' => 'o2', 'new' => 'n2'),
					array('type' => 't2', 'element' => 'e2'),
				),
			),
			'type, element' => array(
				array(
					array('type' => 't1', 'element' => 'e1'),
				),
				array(
					array('type' => 't1', 'element' => 'e2', 'old' => 'o2', 'new' => 'n2'),
					array('type' => 't2', 'element' => 'e2'),
					array('type' => 't2', 'element' => 'e1', 'old' => 'o1', 'new' => 'n1'),
				),
			),
			'type, element, old, new 1' => array(
				array(
					array('type' => 't1', 'element' => 'e1', 'old' => 'o1', 'new' => 'n1'),
				),
				array(
					array('type' => 't1', 'element' => 'e1'),
					array('type' => 't1', 'element' => 'e2', 'old' => 'o2', 'new' => 'n2'),
					array('type' => 't2', 'element' => 'e2'),
					array('type' => 't2', 'element' => 'e1', 'old' => 'o1', 'new' => 'n1'),
				),
			),
			'type, element, old, new 2' => array(
				array(
					array('type' => 't1', 'element' => 'e1', 'old' => 'o1', 'new' => 'n2'),
				),
				array(
					array('type' => 't1', 'element' => 'e1'),
					array('type' => 't1', 'element' => 'e1', 'old' => 'o1', 'new' => 'n1'),
					array('type' => 't1', 'element' => 'e2', 'old' => 'o2', 'new' => 'n2'),
					array('type' => 't2', 'element' => 'e2'),
					array('type' => 't2', 'element' => 'e1', 'old' => 'o1', 'new' => 'n1'),
				),
			),
			'no rules' => array(
				array(),
				array(
					array('type' => 't1', 'element' => 'e1'),
					array('type' => 't1', 'element' => 'e1', 'old' => 'o1', 'new' => 'n1'),
					array('type' => 't1', 'element' => 'e2', 'old' => 'o2', 'new' => 'n2'),
					array('type' => 't2', 'element' => 'e2'),
					array('type' => 't2', 'element' => 'e1', 'old' => 'o1', 'new' => 'n1'),
				),
			),
		);
	}

}
