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

App::uses('Model', 'Model');

/**
 * Class SchemaMigration
 */
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
