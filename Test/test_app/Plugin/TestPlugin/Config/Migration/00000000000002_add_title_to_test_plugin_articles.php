<?php

App::uses('Migration', 'Migrations.Model');

class AddTitleToTestPluginArticles extends Migration {

	public function up() {
		$this->addColumn('test_plugin_articles', 'title', array(
			'type' => 'string',
			'default' => null,
			'null' => false
		));
	}

	public function down() {
		$this->removeColumn('test_plugin_articles', 'title');
	}

}
