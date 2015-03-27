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
