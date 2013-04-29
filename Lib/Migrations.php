<?php

App::uses('CakePlugin', 'Core');
App::uses('ClassRegistry', 'Utility');
App::uses('ConnectionManager', 'Model');
App::uses('Folder', 'Utility');
App::uses('Inflector', 'Utility');
App::uses('MigrationException', 'Migrations.Model/Exception');

class Migrations {

/**
 * The db connection to use.
 *
 * @var string
 */
	public $connection = 'default';

/**
 * Default migration settings.
 *
 * @var array
 */
	protected $_defaults = array(
		'scope' => 'app',
		'direction' => 'up',
		'steps' => false,
		'version' => false,
		'forceAll' => false
	);

/**
 * Holds all available migrations for the different scopes.
 *
 * @var array
 */
	protected $_migrations = array();

/**
 * Holds an instance of SchemaMigration.
 *
 * @var SchemaMigration
 */
	protected $SchemaMigration;

/**
 * Constructor
 *
 * @param string $connection the db connection to be used
 * @param boolean $autoInit If true, the schema_migrations table will be initialized automatically.
 * @return Migrations
 */
	public function __construct($connection = 'default', $autoInit = true) {
		$this->connection = $connection;

		$this->SchemaMigration = ClassRegistry::init('Migrations.SchemaMigration');
		$this->SchemaMigration->setDataSource($this->connection);

		if ($autoInit !== false) {
			$this->_initSchemaMigrations();
		}
	}

/**
 * Migrate a scope in either up or down direction.
 * steps -> the number of migration steps to perform (optional)
 * version -> up until what version you want to migrate (ignored if steps are provided)
 * forceAll -> force to run all migrations (optional) (skips version check)
 *
 * @param array $options
 * @throws InvalidArgumentException
 * @throws Exception
 * @return void
 */
	public function migrate($options = array()) {
		if (!isset($options['direction'])) {
			throw new InvalidArgumentException(__d('migration', 'Missing "direction" option. Provide it with either "up" or "down".'));
		}
		if (isset($options['direction']) && !in_array($options['direction'], array('up', 'down'))) {
			throw new InvalidArgumentException(__d('migration', 'Invalid migration direction "%s". Use either "up" or "down".', $options['direction']));
		}
		if (isset($options['scope']) && $options['scope'] !== 'app') {
			$this->_checkScope($options['scope']);
		}

		/**
		 * @var string  $direction
		 * @var string  $scope
		 * @var integer $steps
		 * @var string  $version
		 * @var boolean $forceAll
		 */
		extract(array_merge($this->_defaults, $options));

		$availableMigrations = $this->_getAvailableMigrations($scope, $direction);
		$offset = 0;

		if ($forceAll === true) {
			$steps = false;
		} else {
			$currentVersion = $this->SchemaMigration->getCurrentVersion($scope);
			if ($currentVersion !== false) {
				$offset = array_search($currentVersion, array_keys($availableMigrations), true) + 1;
			}
		}

		if ($direction === 'down') {
			$offset--;
		}

		if ($version !== false && $version !== 'latest') {
			if (!array_key_exists($version, $availableMigrations)) {
				throw new InvalidArgumentException(__d('migration', 'Invalid migration version "%s".', $version));
			}
			$steps = array_search($version, array_keys($availableMigrations));
			if ($direction === 'up') {
				$steps++;
			}
		}

		if ($steps >= 1) {
			$migrationsToRun = array_slice($availableMigrations, $offset, $steps, true);
		} else {
			$migrationsToRun = array_slice($availableMigrations, $offset, null, true);
		}

		/** @var DboSource $db */
		$db = ConnectionManager::getDataSource($this->connection);

		$db->begin();
		try {
			foreach ($migrationsToRun as $v => $m) {
				$migration = $this->_getMigrationInstance($m);
				$migration->{$direction}();
				$this->SchemaMigration->{$direction}($scope, $v, $m['className']);
			}
		} catch (Exception $e) {
			$db->rollback();

			throw $e;
		}
		$db->commit();

	}

/**
 * Initialize schema_migrations table and run all new migrations of the Migrations plugin.
 *
 * @return void
 */
	protected function _initSchemaMigrations() {
		$options = array(
			'direction' => 'up',
			'scope' => 'Migrations'
		);
		/** @var DboSource $db */
		$db = ConnectionManager::getDataSource($this->connection);
		if (!in_array($db->fullTableName('schema_migrations', false, false), $db->listSources())) {
			$options['forceAll'] = true;
		}
		$this->migrate($options);
	}

/**
 * Check if a scope is available by looking at all registered plugin folders.
 *
 * A plugin has not to be loaded before running its migrations.
 *
 * @param string $scope The scope (plugin name) that should be checked for availability.
 * @throws InvalidArgumentException if the scope is not available
 * @return void
 */
	protected function _checkScope($scope) {
		if (!CakePlugin::loaded(Inflector::camelize($scope))) {
			$pluginPaths = App::path('Plugin');
			$availablePlugins = array();
			foreach ($pluginPaths as $pluginPath) {
				$folder = new Folder($pluginPath, false);
				$pluginDirs = $folder->read()[0];
				$availablePlugins = array_merge($availablePlugins, $pluginDirs);
			}
			if (!in_array(Inflector::camelize($scope), $availablePlugins)) {
				throw new InvalidArgumentException(__d('migration', 'The scope "%s" is not available. Use one of the available scopes instead: %s', array(
					$scope,
					implode(', ', $availablePlugins)
				)));
			}
		}
	}

/**
 * Get all available migrations for a scope from file system.
 * If direction equals "down" then additionally check against migrated versions in schema_migration
 * to only migrate existing ones down.
 * This makes it possible to insert new migrations in between already existing and migrated ones.
 *
 * @param string $scope The scope (app or plugin name) that you want to get migrations from.
 * @param string $direction The migration direction for which to order the available migrations array.
 * @return array all available migrations for this scope
 */
	protected function _getAvailableMigrations($scope, $direction = 'up') {
		if (!isset($this->_migrations[$scope]) || empty($this->_migrations[$scope])) {
			$folder = $this->_getMigrationsFolder($scope);
			if (!$folder) {
				return array();
			}

			$this->_migrations[$scope] = array();
			foreach ($folder->find("^\d+_.*\.php$", true) as $mf) {
				if (preg_match("/^(\d+)_(.*)\.php$/", $mf, $matches) === 1) {
					list($fileName, $version, $name) = $matches;
					$this->_migrations[$scope][$version] = array(
						'path' => $folder->path,
						'fileName' => $fileName,
						'className' => Inflector::camelize($name)
					);
				}
			}
		}

		$migrations = $this->_migrations[$scope];

		if ($direction === 'down') {
			$migrations = array_reverse($migrations);
			$existingMigrations = $this->SchemaMigration->getMigrationsForDown($scope);
			foreach (array_keys($migrations) as $key) {
				if (!array_key_exists($key, $existingMigrations)) {
					unset($migrations[$key]);
				}
			}
		}

		return $migrations;
	}

/**
 * Get a migration folder instance for $scope.
 * If the plugin is not loaded, all available plugin paths will be checked.
 *
 * @param string $scope either `app` or a plugin name
 * @return bool|Folder false if no migration folder has been found or the actual folder instance
 */
	protected function _getMigrationsFolder($scope) {
		$path = APP . 'Config' . DS . 'Migration';

		if ($scope !== 'app') {
			$plugin = Inflector::camelize($scope);

			if (CakePlugin::loaded($plugin)) {
				$path = CakePlugin::path($plugin) . 'Config' . DS . 'Migration' . DS;
			} else {
				foreach (App::path('Plugin') as $pluginPath) {
					$folder = new Folder($pluginPath, false);
					$pluginDirs = $folder->read()[0];
					if (in_array($plugin, $pluginDirs)) {
						$path = $folder->path . $plugin . DS . 'Config' . DS . 'Migration' . DS;
						break;
					}
				}
			}
		}

		if (!is_dir($path)) {
			return false;
		}

		return new Folder($path, false);
	}

/**
 * Get an instance of a migration class.
 *
 * @param array $availableMigration
 * @throws MigrationException
 * @return Migration
 */
	protected function _getMigrationInstance($availableMigration) {
		/**
		 * @var string $path
		 * @var string $fileName
		 * @var string $className
		 */
		extract($availableMigration);
		if (!class_exists($className)) {
			$file = $path . $fileName;
			if (!file_exists($file)) {
				throw new MigrationException(__d('migration', 'File %s not found in path %s.', array(
					$fileName,
					$path
				)));
			}
			include $file;
		}
		if (!class_exists($className)) {
			throw new MigrationException(__d('migration', 'Class %s not found in file %s at path %s.', array(
				$className,
				$fileName,
				$path
			)));
		}

		return new $className(array(
			'connection' => $this->connection
		));
	}

}
