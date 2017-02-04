# Code-Insight

[![Build Status](https://travis-ci.org/console-helpers/code-insight.svg?branch=master)](https://travis-ci.org/console-helpers/code-insight)
[![codecov](https://codecov.io/gh/console-helpers/code-insight/branch/master/graph/badge.svg)](https://codecov.io/gh/console-helpers/code-insight)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/console-helpers/code-insight/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/console-helpers/code-insight/?branch=master)


[![Latest Stable Version](https://poser.pugx.org/console-helpers/code-insight/v/stable)](https://packagist.org/packages/console-helpers/code-insight)
[![Total Downloads](https://poser.pugx.org/console-helpers/code-insight/downloads)](https://packagist.org/packages/console-helpers/code-insight)
[![License](https://poser.pugx.org/console-helpers/code-insight/license)](https://packagist.org/packages/console-helpers/code-insight)

Code-Insight is a tool for static analysis of other project source code.

## Usage

For each analyzed project the `.code-insight.json` file needs to be placed at project root folder (e.g. where `src` folder is). Example configuration file:

```json
{
	"finder": [
		{
			"name": "*.php",
			"in": ["src"]
        }
	],
	"bc_checkers": ["class", "function", "constant"],
	"bc_ignore": [
		{
			"type": "constant.deleted",
			"element": "ADODB_DATE_VERSION",
			"why": "The ADODB DateTime library was removed."
		}
	],
    "class_locator": "vendor/autoload.php"
}
```

Supported settings:

* The `finder` (array) setting is a list of JSON objects. Each object key is a name, and each value an argument for the methods in the [Symfony\\Component\\Finder\\Finder](https://github.com/symfony/finder/blob/master/Finder.php) class. If an array of values is provided for a single key, the method will be called once per value in the array. Note that the paths specified for the `in` method are relative to project base path.
* The `bc_checkers` (array) setting is a list of BC checker names. Possible BC checkers are: `class`, `function`, `constant`, `inportal_class`. If setting isn't specified, then default value of `['class', 'function', 'constant']` is used.
* The `bc_ignore` (array) setting is a list of JSON objects. Each object represents a rule, that prevents a particular BC break or BC break group from being reported. The actual objects to specify in here can be found in output of `bc` command, when executed with `--format=json` option.
* The `class_locator` (string) setting is a path to Composer's `autoload.php` file or file, that returns closure accepting FQCN in 1st argument and returning absolute path with that class declaration.

### Commands

#### The `bc` command

...

#### The `sync` command

...

#### The `report` command

...

#### The `missing-tests` command

...

## Installation

1. download latest released PHAR file from https://github.com/console-helpers/code-insight/releases page.
2. setup auto-completion by placing `eval $(/path/to/code-insight.phar _completion --generate-hook -p code-insight.phar)` in `~/.bashrc`

## Contributing

See [CONTRIBUTING](CONTRIBUTING.md) file.

## License

Code-Insight is released under the BSD-3-Clause License. See the bundled [LICENSE](LICENSE) file for details.
