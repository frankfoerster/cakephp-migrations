<?php

App::uses('Migration', 'Migrations.Model');

class AddTestPluginArticlesTable extends Migration {

	public function up() {
		$this->createTable('test_plugin_articles', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
	}

	public function down() {
		$this->dropTable('test_plugin_articles');
	}

}
