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

App::uses('AppShell', 'Console/Command');
App::uses('Migrations', 'Migrations.Lib');
App::uses('SchemaMigration', 'Migrations.Model');

/**
 * Class MigrationShell
 */
class MigrationShell extends AppShell {

	public $connection = 'default';

	public $scope = 'app';

	protected $_migrationVersion;

	/**
	 * Main shell runner.
	 */
	public function main() {
		$this->nl();
		$this->out(__d('migration_shell', 'Welcome to CakePHP Migrations Shell v%s', $this->_getMigrationVersion()));
		$this->hr();
		$this->out($this->getOptionParser()->help());
	}

	/**
	 * Startup callback
	 */
	public function startup() {
		$task = Inflector::classify($this->command);
		if (isset($this->{$task})) {
			if (isset($this->params['connection'])) {
				$this->{$task}->connection = $this->params['connection'];
			}
			if (isset($this->params['plugin'])) {
				$this->{$task}->scope = $this->params['plugin'];
			}
		}
	}

	/**
	 * Defines and returns the option parse for this shell.
	 *
	 * @return ConsoleOptionParser
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser
			->description(
				__d('migration_shell', 'The migration shell can be used to manage your migrations. If no plugin option is provided, then the command will be run on app level.')
			)
			->addSubCommand('migrate', array(
				'help' => __d('migration_shell', 'Run migrations in the up or down direction.'),
				'parser' => array(
					'description' => array(
						__d('migration_shell', 'Use this command to migrate in the specified direction (up/down).'),
						__d('migration_shell', 'Optionally provide the number of migration steps,'),
						__d('migration_shell', 'or a specfic version to migrate to.')
					),
					'arguments' => array(
						'direction' => array(
							'help' => __d('migration_shell', 'up or down'),
							'required' => true
						),
						'steps' => array(
							'help' => __d('migration_shell', 'number of steps that should be migrated'),
							'required' => false
						)
					),
					'options' => array(
						'plugin' => array(
							'short' => 'p',
							'help' => __d('migration_shell', 'Plugin name to be used.')
						),
						'connection' => array(
							'short' => 'c',
							'default' => 'default',
							'help' => __d('migration_shell', 'The db config to run the migrations on.')
						)
					)
				)
			));

		return $parser;
	}

	/**
	 * The migrate command.
	 *
	 * @return bool
	 */
	public function migrate() {
		$options = array(
			'direction' => $this->args[0],
			'scope' => 'app',
		);
		if (isset($this->params['plugin']) && CakePlugin::loaded($this->params['plugin'])) {
			$options['scope'] = $this->params['plugin'];
		}
		if (isset($this->args[1])) {
			$options['steps'] = (int) $this->args[1];
		}

		try {
			$migrations = new Migrations($this->params['connection']);
			$migrations->migrate($options);
		} catch (Exception $e) {
			$this->out(__d('migration_shell', 'An error occured during the migration.'));
			$this->err($e->getMessage());
			return false;
		}
		$this->out(__d('migration_shell', 'The migration was successful.'));
		/** @var SchemaMigration $sm */
		$sm = ClassRegistry::init('Migrations.SchemaMigration');
		$sm->setDataSource($this->params['connection']);
		$this->out(__d('migration_shell', 'Current Migration version: %s', array($sm->getCurrentVersion($options['scope']))));
		$migrations->clearCache();
		return true;
	}

	/**
	 * Determine the current migration version.
	 *
	 * @return mixed|string
	 */
	protected function _getMigrationVersion() {
		if ($this->_migrationVersion === null) {
			$versionFile = new File(CakePlugin::path('Migrations') . 'version.txt');
			if ($versionFile->exists()) {
				$this->_migrationVersion = $versionFile->read();
			} else {
				$this->_migrationVersion = '0.0.0';
			}
		}
		return $this->_migrationVersion;
	}
}
