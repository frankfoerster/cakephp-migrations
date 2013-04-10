<?php
/**
 *
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the below copyright notice.
 *
 * @copyright     Copyright 2013, Frank Förster (http://frankfoerster.com)
 * @link          http://github.com/frankfoerster/cakephp-migrations
 * @package       Migration
 * @subpackage    Migration.Test.Case
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class AllMigrationTestsTest extends PHPUnit_Framework_TestSuite {

	public static function suite() {
		$suite = new CakeTestSuite('All Migration Tests');

		$path = dirname(__FILE__) . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
