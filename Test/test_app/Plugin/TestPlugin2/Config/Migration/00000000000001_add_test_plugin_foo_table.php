<?php

App::uses('Migration', 'Migrations.Model');

class ClassNameDoesNotMatchFileName extends Migration {

	public function up() {
		$this->createTable('test_plugin_foo', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
	}

	public function down() {
		$this->dropTable('test_plugin_foo');
	}

}
