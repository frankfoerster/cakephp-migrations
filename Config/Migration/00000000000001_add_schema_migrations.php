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

App::uses('Migration', 'Migrations.Model');

class AddSchemaMigrations extends Migration {

	public function up() {
		$this->createTable('schema_migrations', array(
			'id' => array('type' => 'integer', 'null' => false, 'key' => 'primary'),
			'scope' => array('type' => 'string', 'null' => false),
			'version' => array('type' => 'string', 'null' => false),
			'class' => array('type' => 'string', 'null' => false),
			'migrated' => array('type' => 'datetime', 'null' => false),
			'indexes' => array(
				'PRIMARY' => array(
					'column' => 'id',
					'unique' => 1
				)
			)
		));
	}

	public function down() {
		$this->dropTable('schema_migrations');
	}

}
