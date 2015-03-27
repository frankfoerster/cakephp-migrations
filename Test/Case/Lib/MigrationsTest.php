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

App::uses('CakeSchema', 'Model');
App::uses('CakeTestCase', 'TestSuite');
App::uses('ConnectionManager', 'Model');
App::uses('Migrations', 'Migrations.Lib');

/**
 * @property string $connection
 * @property Migrations $Migrations
 */

class MigrationsTest extends CakeTestCase {

/**
 * setUp method
 */
	public function setUp() {
		parent::setUp();

		App::build(array(
			'Plugin' => array(CakePlugin::path('Migrations') . 'Test' . DS . 'test_app' . DS . 'Plugin' . DS),
		), APP::RESET);

		$this->connection = 'test';
		$this->Migrations = new Migrations($this->connection);
	}

/**
 * tearDown method
 */
	public function tearDown() {
		unset($this->Migrations);

		/** @var DboSource $db */
		$db = ConnectionManager::getDataSource($this->connection);
		$tables = $db->listSources();
		$schema = new CakeSchema();
		foreach ($tables as $table) {
			$schema->tables = array($table => array());
			$db->execute($db->dropSchema($schema));
		}

		parent::tearDown();
	}

/**
 * Ensure `schema_migrations` table is initialized automatically.
 */
	public function testInitMigrations() {
		/** @var DboSource $db */
		$db = ConnectionManager::getDataSource($this->connection);

		$this->assertTrue(in_array($db->fullTableName('schema_migrations', false, false), $db->listSources()));
	}

/**
 * testMigrate method
 */
	public function testMigrate() {
		/** @var DboSource $db */
		$db = ConnectionManager::getDataSource($this->connection);
		/** @var SchemaMigration $sm */
		$sm = ClassRegistry::init('Migrations.SchemaMigration');
		$sm->setDataSource($this->connection);

		$this->Migrations->migrate(array(
			'direction' => 'up',
			'scope' => 'TestPlugin',
			'steps' => 1
		));
		$this->assertTrue(in_array($db->fullTableName('test_plugin_articles', false, false), $db->listSources()));
		$this->assertEquals('00000000000001', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'up',
			'scope' => 'TestPlugin',
			'steps' => 2
		));
		$this->assertEquals('00000000000003', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'down',
			'scope' => 'TestPlugin',
			'steps' => 1
		));
		$this->assertEquals('00000000000002', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'down',
			'scope' => 'TestPlugin',
			'steps' => 1
		));
		$this->assertEquals('00000000000001', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'up',
			'scope' => 'TestPlugin',
			'steps' => 2
		));
		$this->assertEquals('00000000000003', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'down',
			'scope' => 'TestPlugin',
			'steps' => 3
		));
		$this->assertFalse($sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'up',
			'scope' => 'TestPlugin'
		));
		$this->assertEquals('00000000000003', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'down',
			'scope' => 'TestPlugin'
		));
		$this->assertFalse($sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'up',
			'scope' => 'TestPlugin'
		));
		$this->assertEquals('00000000000003', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'down',
			'scope' => 'TestPlugin',
			'steps' => 2
		));
		$this->assertEquals('00000000000001', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'down',
			'scope' => 'TestPlugin',
			'steps' => 2
		));
		$this->assertFalse($sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'up',
			'scope' => 'TestPlugin',
			'version' => '00000000000002'
		));
		$this->assertEquals('00000000000002', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'down',
			'scope' => 'TestPlugin',
			'version' => '00000000000001'
		));
		$this->assertEquals('00000000000001', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'up',
			'scope' => 'TestPlugin',
			'version' => '00000000000003'
		));
		$this->assertEquals('00000000000003', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'down',
			'scope' => 'TestPlugin',
			'version' => '00000000000002'
		));
		$this->assertEquals('00000000000002', $sm->getCurrentVersion('TestPlugin'));

		$this->Migrations->migrate(array(
			'direction' => 'down',
			'scope' => 'TestPlugin'
		));
		$this->assertFalse($sm->getCurrentVersion('TestPlugin'));
	}

/**
 * testMigrateThrowsException1 method
 *
 * Throw an exception if no direction is specified.
 *
 * @expectedException InvalidArgumentException
 */
	public function testMigrateThrowsException1() {
		$this->Migrations->migrate();
	}

/**
 * testMigrateThrowsException2 method
 *
 * Throw an exception if the migration direction does not exist.
 *
 * @expectedException InvalidArgumentException
 */
	public function testMigrateThrowsException2() {
		$this->Migrations->migrate(array('direction' => 'invalid_direction'));
	}

/**
 * testMigrateThrowsException3 method
 *
 * Throw an exception if the provided scope is not available.
 *
 * @expectedException InvalidArgumentException
 */
	public function testMigrateThrowsException3() {
		$this->Migrations->migrate(array(
			'direction' => 'up',
			'scope' => 'NotLoadedPlugin'
		));
	}

/**
 * testMigrateThrowsException4 method
 *
 * Throw an exception if the specified version is not available.
 *
 * @expectedException InvalidArgumentException
 */
	public function testMigrateThrowsException4() {
		$this->Migrations->migrate(array(
			'direction' => 'up',
			'scope' => 'TestPlugin',
			'version' => 'nonexistentversion'
		));
	}

/**
 * testMigrateThrowsException5 method
 *
 * Throw an exception if the desired class is not found in the migration file.
 *
 * @expectedException MigrationException
 */
	public function testMigrateThrowsException5() {
		$this->Migrations->migrate(array(
			'direction' => 'up',
			'scope' => 'TestPlugin2'
		));
	}
}
