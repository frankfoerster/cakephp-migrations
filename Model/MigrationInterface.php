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
 * @subpackage    Migration.Model
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

interface MigrationInterface {

/**
 * This method must be implemented in your custom migration.
 * It is called when your migrations run in the "up" direction.
 *
 * @return void
 */
	public function up();

/**
 * This method must be implemented in your custom migration.
 * It is called when your migrations run in the "down" direction.
 *
 * @return void
 */
	public function down();

}
