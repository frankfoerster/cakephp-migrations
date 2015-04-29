<?php
/**
 * Copyright (c) Frank Förster (http://frankfoerster.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Frank Förster (http://frankfoerster.com)
 * @link          http://github.com/frankfoerster/cakephp-migrations
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

class AllMigrationsTest extends PHPUnit_Framework_TestSuite {

	/**
	 * Test suite caller to run all Migration tests.
	 *
	 * @return CakeTestSuite
	 */
	public static function suite() {
		$suite = new CakeTestSuite('All Migrations Tests');

		$path = dirname(__FILE__) . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
