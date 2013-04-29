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
 * @package       Migrations
 * @subpackage    Migrations.Model
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('CakeSchema', 'Model');
App::uses('ColumnAlreadyExistsException', 'Migrations.Model/Exception');
App::uses('IndexAlreadyExistsException', 'Migrations.Model/Exception');
App::uses('MigrationException', 'Migrations.Model/Exception');
App::uses('MigrationInterface', 'Migrations.Model');
App::uses('MissingColumnException', 'Migrations.Model/Exception');
App::uses('MissingIndexException', 'Migrations.Model/Exception');
App::uses('TableAlreadyExistsException', 'Migrations.Model/Exception');

abstract class Migration extends Object implements MigrationInterface {

/**
 * The connection to be used by the migration.
 * You can override this in your custom migration
 * if you don't want to use the default connection.
 *
 * @var string
 */
	public $connection = 'default';

/**
 * Holds the DataSource/DboSource instance
 * that is built with the supplied $connection.
 *
 * @var DboSource
 */
	protected $_db;

/**
 * Holds a CakeSchema instance
 *
 * @var CakeSchema
 */
	protected $_schema;

/**
 * Constructor
 *
 * @param array $options
 * @return Migration
 */
	public function __construct($options = array()) {
		if (isset($options['connection']) && $options['connection'] != '') {
			$this->connection = $options['connection'];
		}
		$this->_db = ConnectionManager::getDataSource($this->connection);
		$this->_db->cacheSources = false;
		$this->_schema = new CakeSchema(array('connection' => $this->connection));
	}

/**
 * Create a new table.
 *
 * @param string $table
 * @param array $fields
 * @throws TableAlreadyExistsException
 * @throws MigrationException if an sql error occured
 * @return Migration
 */
	public function createTable($table, $fields) {
		if (in_array($this->_db->fullTableName($table, false, false), $this->_db->listSources())) {
			throw new TableAlreadyExistsException(__d('migration', 'Table "%s" already exists in database.', $this->_db->fullTableName($table, false, false)));
		}
		$this->_schema->tables = array($table => $fields);
		try {
			$this->_db->execute($this->_db->createSchema($this->_schema));
		} catch (Exception $e) {
			throw new MigrationException(__d('migration', 'SQL Error: %s', $e->getMessage()));
		}
		return $this;
	}

/**
 * Drop an existing table.
 *
 * @param string $table
 * @throws MissingTableException if the table does not exists in the database
 * @throws MigrationException if an sql error occurred
 * @return Migration
 */
	public function dropTable($table) {
		if (!in_array($this->_db->fullTableName($table, false, false), $this->_db->listSources())) {
			throw new MissingTableException(__d('migration', 'Table "%s" does not exist in database.', $this->_db->fullTableName($table, false, false)));
		}
		$this->_schema->tables = array($table => array());
		try {
			$this->_db->execute($this->_db->dropSchema($this->_schema));
		} catch (Exception $e) {
			throw new MigrationException(__d('migration', 'SQL Error: %s', $e->getMessage()));
		}
		return $this;
	}

/**
 * Rename an existing table.
 *
 * @param string $table
 * @param string $newName
 * @throws MissingTableException if the table does not exist in the database
 * @throws TableAlreadyExistsException if a table called $newName already exists in the database
 * @throws MigrationException if an sql error occurred
 * @return Migration
 */
	public function renameTable($table, $newName) {
		$sources = $this->_db->listSources();
		if (!in_array($this->_db->fullTableName($table, false, false), $sources)) {
			throw new MissingTableException(__d('migration', 'Table "%s" does not exist in database.', $this->_db->fullTableName($table, false, false)));
		}
		if (in_array($this->_db->fullTableName($newName, false, false), $sources)) {
			throw new TableAlreadyExistsException(__d('migration', 'Table "%s" already exists in database.', $this->_db->fullTableName($newName, false, false)));
		}
		$sql = "ALTER TABLE {$table} RENAME TO {$newName};";
		try {
			$this->_db->execute($sql);
		} catch (Exception $e) {
			throw new MigrationException(__d('migration', 'SQL Error: %s', $e->getMessage()));
		}
		return $this;
	}

/**
 * Add a new column to an existing table.
 *
 * @param string $table
 * @param string $name
 * @param array $options
 * @throws MissingTableException if the table does not exist in the database
 * @throws ColumnAlreadyExistsException if the column already exists in the table
 * @throws MigrationException if an sql error occured
 * @return Migration
 */
	public function addColumn($table, $name, $options) {
		if (!in_array($this->_db->fullTableName($table, false, false), $this->_db->listSources())) {
			throw new MissingTableException(__d('migration', 'Table "%s" does not exist in database.', $this->_db->fullTableName($table, false, false)));
		}
		$existingColumns = $this->_db->describe($table);
		if (array_key_exists($name, $existingColumns)) {
			throw new ColumnAlreadyExistsException(__d('migration', 'Column "%s" already exists in table "%s".', array($name, $table)));
		}
		try {
			$this->_db->execute($this->_db->alterSchema(array(
				$table => array('add' => array($name => $options))
			)));
		} catch (Exception $e) {
			throw new MigrationException(__d('migration', 'SQL Error: %s', $e->getMessage()));
		}
		return $this;
	}

/**
 * Remove an existing column from a table.
 *
 * @param string $table
 * @param string $name
 * @throws MissingTableException if the table does not exist in the database
 * @throws MissingColumnException if the column does not exist in the table
 * @throws MigrationException if an sql error occurred
 * @return Migration
 */
	public function removeColumn($table, $name) {
		if (!in_array($this->_db->fullTableName($table, false, false), $this->_db->listSources())) {
			throw new MissingTableException(__d('migration', 'Table "%s" does not exist in database.', $this->_db->fullTableName($table, false, false)));
		}
		$existingColumns = $this->_db->describe($table);
		if (!array_key_exists($name, $existingColumns)) {
			throw new MissingColumnException(__d('migration', 'Column "%s" does not exist in table "%s".', array($name, $table)));
		}
		try {
			$this->_db->execute($this->_db->alterSchema(array(
				$table => array('drop' => array($name => array()))
			)));
		} catch (Exception $e) {
			throw new MigrationException(__d('migration', 'SQL Error: %s', $e->getMessage()));
		}
		return $this;
	}

/**
 * Rename an existing column of a table.
 *
 * @param string $table
 * @param string $oldName
 * @param string $newName
 * @throws MissingTableException if the table does not exist in the database
 * @throws MissingColumnException if the column does not exist in the table
 * @throws MigrationException if an sql error occurred
 * @return Migration
 */
	public function renameColumn($table, $oldName, $newName) {
		if (!in_array($this->_db->fullTableName($table, false, false), $this->_db->listSources())) {
			throw new MissingTableException(__d('migration', 'Table "%s" does not exist in database.', $this->_db->fullTableName($table, false, false)));
		}
		$existingColumns = $this->_db->describe($table);
		if (!array_key_exists($oldName, $existingColumns)) {
			throw new MissingColumnException(__d('migration', 'Column "%s" does not exist in table "%s".', array($oldName, $table)));
		}
		try {
			$this->_db->execute($this->_db->alterSchema(array(
				$table => array('change' => array($oldName => array_merge($existingColumns[$oldName], array('name' => $newName))))
			)));
		} catch (Exception $e) {
			throw new MigrationException(__d('migration', 'SQL Error: %s', $e->getMessage()));
		}
		return $this;
	}

/**
 * Change an existing column of a table.
 *
 * @param string $table
 * @param string $name
 * @param array $options
 * @throws MissingTableException if table does not exist in database
 * @throws MissingColumnException if column does not exist in the table
 * @throws MigrationException if an sql error occurred
 * @return Migration
 */
	public function changeColumn($table, $name, $options) {
		if (!in_array($this->_db->fullTableName($table, false, false), $this->_db->listSources())) {
			throw new MissingTableException(__d('migration', 'Table "%s" does not exist in database.', $this->_db->fullTableName($table, false, false)));
		}
		$existingColumns = $this->_db->describe($table);
		if (!array_key_exists($name, $existingColumns)) {
			throw new MissingColumnException(__d('migration', 'Column "%s" does not exist in table "%s".', array($name, $table)));
		}
		$options = array_merge($existingColumns[$name], $options);
		if (isset($options['length']) && $options['length'] !== null && isset($options['type']) && preg_match("/^(date|time|text)/", $options['type']) === 1) {
			$options['length'] = null;
		}
		if (isset($options['type']) && preg_match("/^(date|time|integer|boolean)/", $options['type'])) {
			if (isset($options['collate'])) {
				unset($options['collate']);
			}
			if (isset($options['charset'])) {
				unset($options['charset']);
			}
		}
		if (isset($options['type']) && preg_match("/^(boolean)/", $options['type'])) {
			$options['length'] = 1;
			if (isset($options['default']) && !is_numeric($options['default']) && $options['default'] !== null) {
				$options['default'] = null;
			}
		}
		try {
			$this->_db->execute($this->_db->alterSchema(array(
				$table => array('change' => array($name => $options))
			)));
		} catch (Exception $e) {
			if (
				get_class($this->_db) === 'Postgres' &&
				$existingColumns[$name]['type'] !== $options['type'] &&
				preg_match("/Datatype\smismatch/", $e->getMessage()) === 1
			) {
				throw new MigrationException(__d('migration', 'Typecasting from "%s" to "%s" is not supported natively by CakePHP using PostgreSQL. You have to execute a custom sql query instead.', array($existingColumns[$name]['type'], $options['type'])));
			}
			throw new MigrationException(__d('migration', 'SQL Error: %s', $e->getMessage()));
		}
		return $this;
	}

/**
 * Add an index to an existing table.
 *
 * @param string $table
 * @param string $name
 * @param array $options
 * @throws MissingTableException if table does not exist in database
 * @throws IndexAlreadyExistsException if an index with $name already exists on the table
 * @throws MigrationException if an sql error occurred
 * @return Migration
 */
	public function addIndex($table, $name, $options) {
		if (!in_array($this->_db->fullTableName($table, false, false), $this->_db->listSources())) {
			throw new MissingTableException(__d('migration', 'Table "%s" does not exist in database.', $this->_db->fullTableName($table, false, false)));
		}
		$existingIndexes = $this->_db->index($table);
		if (array_key_exists($name, $existingIndexes)) {
			throw new IndexAlreadyExistsException(__d('migration', 'Index "%s" already exists on table "%s".', array($name, $table)));
		}
		try {
			$this->_db->execute($this->_db->alterSchema(array(
				$table => array('add' => array('indexes' => array($name => $options)))
			)));
		} catch (Exception $e) {
			throw new MigrationException(__d('migration', 'SQL Error: %s', $e->getMessage()));
		}
		return $this;
	}

/**
 * Remove an existing index from a table.
 *
 * @param string $table
 * @param string $name
 * @throws MissingTableException if table does not exist in database
 * @throws MissingIndexException if the index does not exist on the table
 * @throws MigrationException if an sql error occurred
 * @return Migration
 */
	public function removeIndex($table, $name) {
		if (!in_array($this->_db->fullTableName($table, false, false), $this->_db->listSources())) {
			throw new MissingTableException(__d('migration', 'Table "%s" does not exist in database.', $this->_db->fullTableName($table, false, false)));
		}
		$existingIndexes = $this->_db->index($table);
		if (!array_key_exists($name, $existingIndexes)) {
			throw new MissingIndexException(__d('migration', 'Index "%s" does not exist on table "%s".', array($name, $table)));
		}
		try {
			$this->_db->execute($this->_db->alterSchema(array(
				$table => array('drop' => array('indexes' => array($name => array())))
			)));
		} catch (Exception $e) {
			throw new MigrationException(__d('migration', 'SQL Error: %s', $e->getMessage()));
		}
		return $this;
	}

/**
 * Rename an existing index.
 *
 * @param string $table
 * @param string $oldName
 * @param string $newName
 * @throws MissingTableException if table does not exist in database
 * @throws MissingIndexException if the $oldName index does not exist on the table
 * @throws IndexAlreadyExistsException if the $newName index already exists on the table
 * @throws MigrationException if an sql error occurred
 * @return Migration
 */
	public function renameIndex($table, $oldName, $newName) {
		if (!in_array($this->_db->fullTableName($table, false, false), $this->_db->listSources())) {
			throw new MissingTableException(__d('migration', 'Table "%s" does not exist in database.', $this->_db->fullTableName($table, false, false)));
		}
		$existingIndexes = $this->_db->index($table);
		if (get_class($this->_db) === 'Postgres') {
			$oldName = strtolower($oldName);
			$newName = strtolower($newName);
		}
		if (!array_key_exists($oldName, $existingIndexes)) {
			throw new MissingIndexException(__d('migration', 'Index "%s" does not exist on table "%s".', array($oldName, $table)));
		}
		if (array_key_exists($newName, $existingIndexes)) {
			throw new IndexAlreadyExistsException(__d('migration', 'Index "%s" already exists on table "%s".', array($newName, $table)));
		}
		try {
			$this->_db->execute($this->_db->alterSchema(array(
				$table => array(
					'drop' => array('indexes' => array($oldName => array())),
					'add' => array('indexes' => array($newName => $existingIndexes[$oldName]))
				)
			)));
		} catch (Exception $e) {
			throw new MigrationException(__d('migration', 'SQL Error: %s', $e->getMessage()));
		}
		return $this;
	}

}
