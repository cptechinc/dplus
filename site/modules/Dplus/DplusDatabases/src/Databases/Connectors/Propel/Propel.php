<?php namespace Dplus\Databases\Connectors\Propel;
// Propel ORM Library
use Propel\Runtime\Connection\ConnectionManagerSingle;
use Propel\Runtime\Propel as PropelRuntime;
use Propel\Runtime\ServiceContainer\ServiceContainerInterface;
use Propel\Runtime\Connection\ConnectionInterface;
// ProcessWire
use ProcessWire\Config;

/**
 * Propel
 * Wrapper Class for providing functions to create Propel ORM connections
 */
class Propel {
	/**
	 * Return ConnectionManager
	 * @param  Config $db
	 * @return ConnectionManagerSingle
	 */
	public static function propelConnectionManager(Config $db) {
		$manager = new ConnectionManagerSingle();
		$manager->setConfiguration(self::propelConfiguration($db));
		return $manager;
	}

	/**
	 * Returns Propel connection Configuration
	 * @param  Config $db
	 * @return array
	 */
	public static function propelConfiguration(Config $db) {
		return [
			'classname' => 'Propel\\Runtime\\Connection\\ConnectionWrapper',
			'dsn' => "mysql:host=$db->dbHost;dbname=$db->dbName",
			'user' => $db->dbUser,
			'password' => $db->dbPass,
			'attributes' => [
				'ATTR_EMULATE_PREPARES' => false,
				'ATTR_TIMEOUT' => 30,
			],
			'model_paths' => [
				0 => 'src',
				1 => 'vendor',
			],
		];
	}

	/**
	 * Return Service Container
	 * @return ServiceContainerInterface
	 */
	public static function getServiceContainer() {
		return PropelRuntime::getServiceContainer();
	}

	/**
	 * Return Write Connection
	 * @param  string $name
	 * @return ConnectionInterface
	 */
	public static function getConnection($name) {
		return PropelRuntime::getConnection($name);
	}

	/**
	 * Return Connection Interface for debug
	 * @return ConnectionInterface
	 */
	public static function getConnectionDebug($name) {
		$conn = self::getConnection($name);
		$conn->useDebug(true);
		return $conn;
	}
}