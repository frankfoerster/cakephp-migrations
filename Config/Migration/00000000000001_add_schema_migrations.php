<?php

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
