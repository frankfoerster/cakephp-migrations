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

App::uses('Model', 'Model');

class SchemaMigration extends Model {

/**
 * Retrieve the current migration version for $scope.
 *
 * @param string $scope
 * @return string
 */
	public function getCurrentVersion($scope = 'app') {
		return $this->field('version',
			array(
				"{$this->alias}.scope" => $scope
			),
			"{$this->alias}.version DESC"
		);
	}

/**
 * Retrieve all existing migrations that can be migrated "down".
 *
 * @param string $scope
 * @return array
 */
	public function getMigrationsForDown($scope = 'app') {
		return $this->find('list', array(
			'fields' => array('version', 'class'),
			'conditions' => array(
				"{$this->alias}.scope" => $scope
			),
			'order' => "{$this->alias}.version DESC"
		));
	}

/**
 * Add a new migration entry for a scope with specified version and className.
 *
 * @param string $scope
 * @param string $version
 * @param string $className
 */
	public function up($scope, $version, $className) {
		$this->create();
		$this->save(array(
			'scope' => $scope,
			'version' => $version,
			'migrated' => date('Y-m-d H:i:s'),
			'class' => $className
		));
	}

/**
 * Remove an existing migration $version for $scope.
 *
 * @param string $scope
 * @param string $version
 */
	public function down($scope, $version) {
		$migrationID = $this->field('id', array(
			"{$this->alias}.scope" => $scope,
			"{$this->alias}.version" => $version
		));
		$this->delete($migrationID);
	}

}
