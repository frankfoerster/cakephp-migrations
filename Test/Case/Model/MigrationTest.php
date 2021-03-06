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
App::uses('Migration', 'Migrations.Model');

/**
 * Class TestMigration
 */
class TestMigration extends Migration {

/**
 * @var string
 */
	public $connection = 'test';

/**
 * Migrate up
 */
	public function up() {
	}

/**
 * Migrate down
 */
	public function down() {
	}

/**
 * Get the configured DataSource/DboSource.
 *
 * @return DataSource|DboSource
 */
	public function getDb() {
		return $this->_db;
	}

/**
 * Get the current Schema instance.
 *
 * @return CakeSchema
 */
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
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false)
			))
			->createTable('tests', array(
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
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false)
			))
			->renameTable('tests', 'modified_tests');

		$this->assertFalse(in_array($this->db->fullTableName('tests', false, false), $this->db->listSources()));
		$this->assertTrue(in_array($this->db->fullTableName('modified_tests', false, false), $this->db->listSources()));
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
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false)
			))
			->createTable('modified_tests', array(
				'id' => array('type' => 'integer', 'null' => false)
			))
			->renameTable('tests', 'modified_tests');
	}

/**
 * testAddColumn method
 */
	public function testAddColumn() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false)
			))
			->addColumn('tests', 'title', array(
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
		$this->Migration->addColumn('tests', 'id', array('type' => 'integer'));
	}

/**
 * testAddColumnThrowsException2 method
 *
 * @expectedException ColumnAlreadyExistsException
 */
	public function testAddColumnThrowsException2() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false)
			))
			->addColumn('tests', 'id', array('type' => 'integer'));
	}

/**
 * testAddColumnThrowsException3 method
 *
 * @expectedException MigrationException
 */
	public function testAddColumnThrowsException3() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false)
			))
			->addColumn('tests', 'title', array('type' => 'invalid_type'));
	}

/**
 * testRemoveColumn method
 */
	public function testRemoveColumn() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false)
			))
			->removeColumn('tests', 'title');

		$this->assertArrayNotHasKey('title', $this->db->describe('tests'));
	}

/**
 * testRemoveColumnThrowsException1 method
 *
 * @expectedException MissingTableException
 */
	public function testRemoveColumnThrowsException1() {
		$this->Migration->removeColumn('tests', 'id');
	}

/**
 * testRemoveColumnThrowsException2 method
 *
 * @expectedException MissingColumnException
 */
	public function testRemoveColumnThrowsException2() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false)
			))
			->removeColumn('tests', 'title');
	}

/**
 * testRemoveColumnThrowsException3 method
 *
 * @expectedException MigrationException
 */
	public function testRemoveColumnThrowsException3() {
		$this->skipIf(get_class($this->db) === 'Postgres', __d('migration', 'Skipped on PostgreSQL'));
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false)
			))
			->removeColumn('tests', 'id');
	}

/**
 * testRenameColumn method
 */
	public function testRenameColumn() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false)
			))
			->renameColumn('tests', 'title', 'modified_title');

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
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false)
			))
			->renameColumn('tests', 'foo', 'bar');
	}

/**
 * testChangeColumn method
 */
	public function testChangeColumnStringLength() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false)
			))
			->changeColumn('tests', 'title', array('length' => 60)); // string length 255 -> 60

		$fields = $this->db->describe('tests');
		$this->assertEquals(60, $fields['title']['length']);
	}

/**
 * testChangeColumnStringToText method
 */
	public function testChangeColumnStringToText() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false)
			))
			->changeColumn('tests', 'title', array('type' => 'text'));

		$fields = $this->db->describe('tests');
		$this->assertEquals('text', $fields['title']['type']);
		if (get_class($this->db) !== 'Postgres') {
			$this->assertNull($fields['title']['length']);
		}
	}

/**
 * testChangeColumnTextToString method
 */
	public function testChangeColumnTextToString() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'text', 'null' => false)
			))
			->changeColumn('tests', 'title', array(
				'type' => 'string',
				'length' => 255
			));

		$fields = $this->db->describe('tests');
		$this->assertEquals('string', $fields['title']['type']);
		$this->assertEquals(255, $fields['title']['length']);
	}

/**
 * testChangeColumnStringToDatetime method
 */
	public function testChangeColumnStringToDatetime() {
		$this->skipIf(get_class($this->db) === 'Postgres', __d('migration', 'Skipped on PostgreSQL'));
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false)
			))
			->changeColumn('tests', 'title', array(
				'type' => 'datetime',
				'collate' => 'utf8_unicode', // this should be ignored
				'charset' => 'utf8' // this should be ignored
			));

		$fields = $this->db->describe('tests');
		$this->assertEquals('datetime', $fields['title']['type']);
		$this->assertNull($fields['title']['length']);
		$this->assertArrayNotHasKey('collate', $fields['title']);
		$this->assertArrayNotHasKey('charset', $fields['title']);
	}

/**
 * testChangeColumnDatetimeToTime method
 */
	public function testChangeColumnDatetimeToTime() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'modified' => array('type' => 'datetime', 'null' => false)
			))
			->changeColumn('tests', 'modified', array(
				'type' => 'time',
				'collate' => 'utf8_unicode', // this should be ignored
				'charset' => 'utf8' // this should be ignored
			));

		$fields = $this->db->describe('tests');
		$this->assertEquals('time', $fields['modified']['type']);
		$this->assertNull($fields['modified']['length']);
		$this->assertArrayNotHasKey('collate', $fields['modified']);
		$this->assertArrayNotHasKey('charset', $fields['modified']);
	}

/**
 * testChangeColumnTimeToInteger method
 */
	public function testChangeColumnTimeToInteger() {
		$this->skipIf(get_class($this->db) === 'Postgres', __d('migration', 'Skipped on PostgreSQL'));
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'modified' => array('type' => 'time', 'null' => false)
			))
			->changeColumn('tests', 'modified', array(
				'type' => 'integer',
				'length' => 5,
				'collate' => 'utf8_unicode', // this should be ignored
				'charset' => 'utf8' // this should be ignored
			));

		$fields = $this->db->describe('tests');
		$this->assertEquals('integer', $fields['modified']['type']);
		$this->assertEquals(5, $fields['modified']['length']);
		$this->assertArrayNotHasKey('collate', $fields['modified']);
		$this->assertArrayNotHasKey('charset', $fields['modified']);
	}

/**
 * testChangeColumnIntegerToBoolean method
 */
	public function testChangeColumnIntegerToBoolean() {
		$this->skipIf(get_class($this->db) === 'Postgres', __d('migration', 'Skipped on PostgreSQL'));
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'active' => array('type' => 'integer', 'null' => false)
			))
			->changeColumn('tests', 'active', array(
				'type' => 'boolean',
				'length' => 20, // this should be ignored and set to 1
				'default' => 'foo', // this should be ignored and set to default = null
				'collate' => 'utf8_unicode', // this should be ignored
				'charset' => 'utf8' // this should be ignored
			));

		$fields = $this->db->describe('tests');
		$this->assertEquals('boolean', $fields['active']['type']);
		$this->assertEquals(1, $fields['active']['length']);
		$this->assertArrayNotHasKey('collate', $fields['active']);
		$this->assertArrayNotHasKey('charset', $fields['active']);
	}

/**
 * testChangeColumnBooleanDefaultOne method
 */
	public function testChangeColumnBooleanDefaultOne() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'active' => array('type' => 'boolean', 'default' => 0, 'null' => false)
			))
			->changeColumn('tests', 'active', array(
				'type' => 'boolean',
				'default' => 1,
			));

		$fields = $this->db->describe('tests');
		$this->assertEquals('boolean', $fields['active']['type']);
		if (get_class($this->db) !== 'Postgres') {
			$this->assertEquals(1, $fields['active']['length']);
		}
		$this->assertEquals(1, $fields['active']['default']);
	}

/**
 * testChangeColumnBooleanDefaultZero method
 */
	public function testChangeColumnBooleanDefaultZero() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'active' => array('type' => 'boolean', 'default' => 1, 'null' => false)
			))
			->changeColumn('tests', 'active', array(
				'type' => 'boolean',
				'default' => 0,
			));
		$fields = $this->db->describe('tests');
		$this->assertEquals('boolean', $fields['active']['type']);
		if (get_class($this->db) !== 'Postgres') {
			$this->assertEquals(1, $fields['active']['length']);
		}
		$this->assertEquals(0, $fields['active']['default']);
	}

/**
 * testChangeColumnBooleanDefaultNull method
 */
	public function testChangeColumnBooleanDefaultNull() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'active' => array('type' => 'boolean', 'default' => 1, 'null' => false)
			))
			->changeColumn('tests', 'active', array(
				'type' => 'boolean',
				'default' => null,
			));

		$fields = $this->db->describe('tests');
		$this->assertEquals('boolean', $fields['active']['type']);
		if (get_class($this->db) !== 'Postgres') {
			$this->assertEquals(1, $fields['active']['length']);
			$this->assertNull($fields['active']['default']);
		} else {
			$this->assertEquals('', $fields['active']['default']);
		}
	}

/**
 * testChangeColumnBooleanDefaultRewrittenToNull method
 *
 * Defaults other than 0|1|null for boolean type should be rewritten to null.
 */
	public function testChangeColumnBooleanDefaultRewrittenToNull() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'active' => array('type' => 'boolean', 'default' => 1, 'null' => false)
			))
			->changeColumn('tests', 'active', array(
				'type' => 'boolean',
				'default' => 'silly default',
			));

		$fields = $this->db->describe('tests');
		$this->assertEquals('boolean', $fields['active']['type']);
		if (get_class($this->db) !== 'Postgres') {
			$this->assertEquals(1, $fields['active']['length']);
			$this->assertNull($fields['active']['default']);
		} else {
			$this->assertEquals('', $fields['active']['default']);
		}
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
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false)
			))
			->changeColumn('tests', 'non_existent', array(
				'type' => 'integer'
			));
	}

/**
 * testChangeColumnThrowsException3 method
 *
 * @expectedException MigrationException
 */
	public function testChangeColumnThrowsException3() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false)
			))
			->changeColumn('tests', 'title', array(
				'type' => 'invalid_type'
			));
	}

/**
 * testAddIndex method
 */
	public function testAddIndex() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false)
			))
			->addIndex('tests', 'PRIMARY', array(
				'column' => 'id',
				'unique' => 1
			));

		$indexes = $this->db->index('tests');
		$this->assertArrayHasKey('PRIMARY', $indexes);
		$this->assertEquals('id', $indexes['PRIMARY']['column']);
		$this->assertArrayHasKey('unique', $indexes['PRIMARY']);
		$this->assertEquals(1, $indexes['PRIMARY']['unique']);
	}

/**
 * testAddIndexThrowsException1 method
 *
 * @expectedException MissingTableException
 */
	public function testAddIndexThrowsException1() {
		$this->Migration->addIndex('tests', 'TEST_INDEX', array());
	}

/**
 * testAddIndexActionThrowsException2 method
 *
 * @expectedException IndexAlreadyExistsException
 */
	public function testAddIndexActionThrowsException2() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false),
				'indexes' => array(
					'PRIMARY' => array(
						'column' => 'id',
						'unique' => 1
					)
				)
			))
			->addIndex('tests', 'PRIMARY', array());
	}

/**
 * testAddIndexThrowsException3 method
 *
 * @expectedException MigrationException
 */
	public function testAddIndexThrowsException3() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false)
			))
			->addIndex('tests', 'INVALID_INDEX', array(
				'column' => 'non_existent_column'
			));
	}

/**
 * testRemoveIndex method
 */
	public function testRemoveIndex() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false),
				'indexes' => array(
					'PRIMARY' => array(
						'column' => 'id',
						'unique' => 1
					)
				)
			))
			->removeIndex('tests', 'PRIMARY');

		$indexes = $this->db->index($this->db->fullTableName('tests', false, false));
		$this->assertArrayNotHasKey('PRIMARY', $indexes);
	}

/**
 * testRemoveIndexThrowsException1 method
 *
 * @expectedException MissingTableException
 */
	public function testRemoveIndexThrowsException1() {
		$this->Migration->removeIndex('tests', 'PRIMARY');
	}

/**
 * testRemoveIndexThrowsException2 method
 *
 * @expectedException MissingIndexException
 */
	public function testRemoveIndexThrowsException2() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false)
			))
			->removeIndex('tests', 'PRIMARY');
	}

/**
 * testRenameIndex method
 */
	public function testRenameIndex() {
		$this->Migration->createTable('tests', array(
			'id' => array('type' => 'integer', 'null' => false),
			'title' => array('type' => 'string', 'length' => 255, 'null' => false),
			'subtitle' => array('type' => 'string', 'length' => 255, 'null' => false),
			'indexes' => array(
				'TITLE_UNIQUE' => array(
					'column' => 'title',
					'unique' => 1
				)
			)
		));
		$oldIndexes = $this->db->index('tests');

		$this->Migration->renameIndex('tests', 'TITLE_UNIQUE', 'SUBTITLE_UNIQUE');
		$newIndexes = $this->db->index('tests');

		if (get_class($this->db) !== 'Postgres') {
			$this->assertArrayNotHasKey('TITLE_UNIQUE', $newIndexes);
			$this->assertArrayHasKey('SUBTITLE_UNIQUE', $newIndexes);
			$this->assertEquals($oldIndexes['TITLE_UNIQUE'], $newIndexes['SUBTITLE_UNIQUE']);
		} else {
			$this->assertArrayNotHasKey('title_unique', $newIndexes);
			$this->assertArrayHasKey('subtitle_unique', $newIndexes);
			$this->assertEquals($oldIndexes['title_unique'], $newIndexes['subtitle_unique']);
		}
	}

/**
 * testRenameIndexThrowsException1 method
 *
 * @expectedException MissingTableException
 */
	public function testRenameIndexThrowsException1() {
		$this->Migration->renameIndex('tests', 'TITLE_UNIQUE', 'SUPER_TITLE_UNIQUE');
	}

/**
 * testRenameIndexThrowsException2 method
 *
 * @expectedException MissingIndexException
 */
	public function testRenameIndexThrowsException2() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false)
			))
			->renameIndex('tests', 'TITLE_UNIQUE', 'SUPER_TITLE_UNIQUE');
	}

/**
 * testRenameIndexThrowsException3 method
 *
 * @expectedException IndexAlreadyExistsException
 */
	public function testRenameIndexThrowsException3() {
		$this->Migration
			->createTable('tests', array(
				'id' => array('type' => 'integer', 'null' => false),
				'title' => array('type' => 'string', 'length' => 255, 'null' => false),
				'subtitle' => array('type' => 'string', 'length' => 255, 'null' => false),
				'indexes' => array(
					'TITLE_UNIQUE' => array(
						'column' => 'title',
						'unique' => 1
					),
					'SUBTITLE_UNIQUE' => array(
						'column' => 'subtitle',
						'unique' => 1
					)
				)
			))
			->renameIndex('tests', 'TITLE_UNIQUE', 'SUBTITLE_UNIQUE');
	}
}
