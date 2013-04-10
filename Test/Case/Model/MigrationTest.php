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
 * @subpackage    Migration.Test.Case.Model
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('CakeSchema', 'Model');
App::uses('Migration', 'Migration.Model');

class TestMigration extends Migration {

	public $connection = 'test';

	public function up() {
	}

	public function down() {
	}

	public function getDb() {
		return $this->_db;
	}

	public function getSchema() {
		return $this->_schema;
	}

}

/**
 * @property TestMigration $Migration
 * @property DboSource $db
 */

class MigrationTest extends CakeTestCase {

/**
 * setUp method
 */
	public function setUp() {
		parent::setUp();

		$this->Migration = new TestMigration();
		$this->db = $this->Migration->getDb();
	}

/**
 * tearDown method
 *
 * Since the MigrationTest does not use any fixtures, we have to manually drop
 * all tables from the test db after each test.
 */
	public function tearDown() {
		$tables = $this->db->listSources();
		$schema = $this->Migration->getSchema();
		foreach ($tables as $table) {
			$schema->tables = array($table => array());
			$this->db->execute($this->db->dropSchema($schema));
		}
		unset($this->db);
		unset($this->Migration);

		parent::tearDown();
	}

/**
 * testCreateTable method
 */
	public function testCreateTable() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->assertTrue(in_array($this->db->fullTableName('tests', false, false), $this->db->listSources()));
	}

/**
 * testCreateTableThrowsException1 method
 *
 * @expectedException TableAlreadyExistsException
 */
	public function testCreateTableThrowsException1() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
	}

/**
 * testCreateTableThrowsException2 method
 *
 * @expectedException MigrationException
 */
	public function testCreateTableThrowsException2() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'invalid_type')
		));
	}

/**
 * testDropTable method
 */
	public function testDropTable() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->assertTrue(in_array($this->db->fullTableName('tests', false, false), $this->db->listSources()));

		$this->Migration->dropTable('tests');
		$this->assertFalse(in_array($this->db->fullTableName('tests', false, false), $this->db->listSources()));
	}

/**
 * testDropTableThrowsException1 method
 *
 * @expectedException MissingTableException
 */
	public function testDropTableThrowsException1() {
		$this->Migration->dropTable('tests');
	}

/**
 * testRenameTable method
 */
	public function testRenameTable() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->Migration->renameTable('tests', 'modified_tests');
		$this->assertFalse(in_array('test', $this->db->listSources()));
		$this->assertTrue(in_array('modified_tests', $this->db->listSources()));
	}

/**
 * testRenameTableThrowsException1 method
 *
 * @expectedException MissingTableException
 */
	public function testRenameTableThrowsException1() {
		$this->Migration->renameTable('test', 'modified_tests');
	}

/**
 * testRenameTableThrowsException2 method
 *
 * @expectedException TableAlreadyExistsException
 */
	public function testRenameTableThrowsException2() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->Migration->createTable('modified_tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->Migration->renameTable('tests', 'modified_tests');
	}

/**
 * testAddColumn method
 */
	public function testAddColumn() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->Migration->addColumn('tests', 'title', array(
			'type' => 'string',
			'length' => 255,
			'null' => false
		));
		$this->assertArrayHasKey('title', $this->db->describe('tests'));
	}

/**
 * testAddColumnThrowsException1 method
 *
 * @expectedException MissingTableException
 */
	public function testAddColumnThrowsException1() {
		$this->Migration->addColumn('tests', 'id', array(
			'type' => 'integer'
		));
	}

/**
 * testAddColumnThrowsException2 method
 *
 * @expectedException ColumnAlreadyExistsException
 */
	public function testAddColumnThrowsException2() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->Migration->addColumn('tests', 'id', array(
			'type' => 'integer'
		));
	}

/**
 * testAddColumnThrowsException3 method
 *
 * @expectedException MigrationException
 */
	public function testAddColumnThrowsException3() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->Migration->addColumn('tests', 'title', array(
			'type' => 'invalid_type'
		));
	}

/**
 * testRemoveColumn method
 */
	public function testRemoveColumn() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false),
			'title' => array('type' => 'string', 'length' => 255, 'null' => false)
		));
		$this->Migration->removeColumn('tests', 'title');
		$this->assertArrayNotHasKey('title', $this->db->describe('tests'));
	}

/**
 * testRemoveColumnThrowsException1 method
 *
 * @expectedException MissingTableException
 */
	public function testRemoveColumnThrowsException1() {
		$this->Migration->removeColumn('tests', 'id', array(
			'type' => 'integer'
		));
	}

/**
 * testRemoveColumnThrowsException2 method
 *
 * @expectedException MissingColumnException
 */
	public function testRemoveColumnThrowsException2() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->Migration->removeColumn('tests', 'title');
	}

/**
 * testRemoveColumnThrowsException3 method
 *
 * @expectedException MigrationException
 */
	public function testRemoveColumnThrowsException3() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->Migration->removeColumn('tests', 'id');
	}

/**
 * testRenameColumn method
 */
	public function testRenameColumn() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false),
			'title' => array('type' => 'string', 'length' => 255, 'null' => false)
		));
		$this->Migration->renameColumn('tests', 'title', 'modified_title');
		$this->assertArrayNotHasKey('title', $this->db->describe('tests'));
		$this->assertArrayHasKey('modified_title', $this->db->describe('tests'));
	}

/**
 * testRenameColumnThrowsException1 method
 *
 * @expectedException MissingTableException
 */
	public function testRenameColumnThrowsException1() {
		$this->Migration->renameColumn('non_existant', 'title', 'foo');
	}

/**
 * testRenameColumnThrowsException1 method
 *
 * @expectedException MissingColumnException
 */
	public function testRenameColumnThrowsException2() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false)
		));
		$this->Migration->renameColumn('tests', 'foo', 'bar');
	}

/**
 * testChangeColumn method
 */
	public function testChangeColumn() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false),
			'title' => array('type' => 'string', 'length' => 255, 'null' => false)
		));

		// string length 255 -> 60
		$this->Migration->changeColumn('tests', 'title', array(
			'length' => 60
		));
		$fields = $this->db->describe('tests');
		$this->assertEqual(60, $fields['title']['length']);

		// type string -> text
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'text',
		));
		$fields = $this->db->describe('tests');
		$this->assertEqual('text', $fields['title']['type']);
		$this->assertNull($fields['title']['length']);

		// type text -> string
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'string',
			'length' => 255
		));
		$fields = $this->db->describe('tests');
		$this->assertEqual('string', $fields['title']['type']);
		$this->assertEqual(255, $fields['title']['length']);

		// type string -> datetime
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'datetime',
			'collate' => 'utf8_unicode', // this should be ignored
			'charset' => 'utf8' // this should be ignored
		));
		$fields = $this->db->describe('tests');
		$this->assertEqual('datetime', $fields['title']['type']);
		$this->assertNull($fields['title']['length']);
		$this->assertArrayNotHasKey('collate', $fields['title']);
		$this->assertArrayNotHasKey('charset', $fields['title']);

		// type datetime -> time
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'time',
			'collate' => 'utf8_unicode', // this should be ignored
			'charset' => 'utf8' // this should be ignored
		));
		$fields = $this->db->describe('tests');
		$this->assertEqual('time', $fields['title']['type']);
		$this->assertNull($fields['title']['length']);
		$this->assertArrayNotHasKey('collate', $fields['title']);
		$this->assertArrayNotHasKey('charset', $fields['title']);

		// type time -> integer
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'integer',
			'length' => 5,
			'collate' => 'utf8_unicode', // this should be ignored
			'charset' => 'utf8' // this should be ignored
		));
		$fields = $this->db->describe('tests');
		$this->assertEqual('integer', $fields['title']['type']);
		$this->assertEqual(5, $fields['title']['length']);
		$this->assertArrayNotHasKey('collate', $fields['title']);
		$this->assertArrayNotHasKey('charset', $fields['title']);

		// type integer -> boolean
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'boolean',
			'length' => 20, // this should be ignored and set to 1
			'default' => 'foo', // this should be ignored and set to default = null
			'collate' => 'utf8_unicode', // this should be ignored
			'charset' => 'utf8' // this should be ignored
		));
		$fields = $this->db->describe('tests');
		$this->assertEqual('boolean', $fields['title']['type']);
		$this->assertEqual(1, $fields['title']['length']);
		$this->assertArrayNotHasKey('collate', $fields['title']);
		$this->assertArrayNotHasKey('charset', $fields['title']);

		// type boolean
		// test default 1 works
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'boolean',
			'default' => 1,
		));
		$fields = $this->db->describe('tests');
		$this->assertEqual('boolean', $fields['title']['type']);
		$this->assertEqual(1, $fields['title']['length']);
		$this->assertEqual(1, $fields['title']['default']);

		// type boolean
		// test default 0 works
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'boolean',
			'default' => 0,
		));
		$fields = $this->db->describe('tests');
		$this->assertEqual('boolean', $fields['title']['type']);
		$this->assertEqual(1, $fields['title']['length']);
		$this->assertEqual(0, $fields['title']['default']);

		// type boolean
		// test default null works
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'boolean',
			'default' => null,
		));
		$fields = $this->db->describe('tests');
		$this->assertEqual('boolean', $fields['title']['type']);
		$this->assertEqual(1, $fields['title']['length']);
		$this->assertNull($fields['title']['default']);

		// type boolean
		// test default other then 0|1|null gets rewritten to null
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'boolean',
			'default' => 'foo again',
		));
		$fields = $this->db->describe('tests');
		$this->assertEqual('boolean', $fields['title']['type']);
		$this->assertEqual(1, $fields['title']['length']);
		$this->assertNull($fields['title']['default']);
	}

/**
 * testChangeColumnThrowsException1 method
 *
 * @expectedException MissingTableException
 */
	public function testChangeColumnThrowsException1() {
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'string'
		));
	}

/**
 * testChangeColumnThrowsException2 method
 *
 * @expectedException MissingColumnException
 */
	public function testChangeColumnThrowsException2() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false),
			'title' => array('type' => 'string', 'length' => 255, 'null' => false)
		));
		$this->Migration->changeColumn('tests', 'non_existent', array(
			'type' => 'integer'
		));
	}

/**
 * testChangeColumnThrowsException3 method
 *
 * @expectedException MigrationException
 */
	public function testChangeColumnThrowsException3() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false),
			'title' => array('type' => 'string', 'length' => 255, 'null' => false)
		));
		$this->Migration->changeColumn('tests', 'title', array(
			'type' => 'invalid_type'
		));
	}

}