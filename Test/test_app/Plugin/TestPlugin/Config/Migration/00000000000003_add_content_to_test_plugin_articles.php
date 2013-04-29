<?php

App::uses('Migration', 'Migrations.Model');

class AddContentToTestPluginArticles extends Migration {

	public function up() {
		$this->addColumn('test_plugin_articles', 'content', array(
			'type' => 'text',
			'default' => null,
			'null' => false
		));
	}

	public function down() {
		$this->removeColumn('test_plugin_articles', 'content');
	}

}
