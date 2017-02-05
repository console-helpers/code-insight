<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\BackwardsCompatibility\Reporter;


class HtmlReporter extends AbstractReporter
{

	/**
	 * Returns reporter name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'html';
	}

	/**
	 * Generates report.
	 *
	 * @param array $bc_breaks BC breaks.
	 *
	 * @return string
	 */
	public function generate(array $bc_breaks)
	{
		if ( !$bc_breaks ) {
			return '<h1>No backwards compatibility breaks detected.</h1>';
		}

		$ret = <<<HTML
<html>
	<head>
		<style type="text/css">
			ol.bc-report { font-family: monospace; }
			ol.bc-report li { padding: 4px; margin-bottom: 8px; }
			ol.bc-report li:nth-child(odd) { background-color: lightgray; }
		</style>
	</head>
	<body>
		<h1>Backward compatibility breaks:</h1>
HTML;

		foreach ( $this->groupByType($bc_breaks) as $bc_break => $incidents ) {
			$ret .= PHP_EOL;

			$bc_break = ucwords(str_replace(array('.', '_'), ' ', $bc_break));
			$ret .= "\t\t" . '<h2>' . $bc_break . ' (' . count($incidents) . ')</h2>' . PHP_EOL;
			$ret .= "\t\t" . '<ol class="bc-report">' . PHP_EOL;

			foreach ( $this->sortByElement($incidents) as $incident_data ) {
				if ( array_key_exists('old', $incident_data) ) {
					$ret .= "\t\t\t" . '<li>' . PHP_EOL;
					$ret .= "\t\t\t\t" . '<strong>' . $incident_data['element'] . '</strong><br/>' . PHP_EOL;
					$ret .= "\t\t\t\t" . 'OLD: ' . $incident_data['old'] . '<br/>' . PHP_EOL;
					$ret .= "\t\t\t\t" . 'NEW: ' . $incident_data['new'] . '<br/>' . PHP_EOL;
					$ret .= "\t\t\t" . '</li>' . PHP_EOL;
				}
				else {
					$ret .= "\t\t\t" . '<li>' . $incident_data['element'] . '</li>' . PHP_EOL;
				}
			}

			$ret .= "\t\t" . '</ol>';
		}

		$ret .= PHP_EOL;
		$ret .= "\t" . '</body>' . PHP_EOL;
		$ret .= '</html>' . PHP_EOL;

		return $ret;
	}

}
