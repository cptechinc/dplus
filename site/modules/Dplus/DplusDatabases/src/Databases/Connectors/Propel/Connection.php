<?php namespace Dplus\Databases\Connectors\Propel;
// ProcessWire
use ProcessWire\Config;
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Connection
 * 
 * Wrapper class for creating a Propel ORM connection
 * @property string               $name        Connection Name
 * @property Config               $dbconfig    DB Credentials
 * @property bool                 $isDefault   Is default Connection
 * @property ConnectionInterface  $connection  Propel Connection
 */
class Connection extends WireData {
	public function __construct() {
		$this->name   = '';
		$this->dbconfig = false;
		$this->isDefault = false;
		$this->connection = false;
	}

	/**
	 * Set Database Credentials
	 * @param  Config $config
	 * @return void
	 */
	public function setDbconfig(Config $config) {
		$this->dbconfig = $config;
	}

	/**
	 * Set if Connection should be default
	 * @param  bool $isDefault
	 * @return void
	 */
	public function setIsDefault($isDefault = false) {
		$this->isDefault = $isDefault;
	}


	/**
	 * Makes Propel Connection to database
	 * @return bool
	 */
	public function connect() {
		if ($this->connection) {
			return true;
		}
		$manager = Propel::propelConnectionManager($this->dbconfig);
		$service = Propel::getServiceContainer();
		$service->checkVersion('2.0.0-dev');
		$service->setAdapterClass($this->name, 'mysql');
		$service->setConnectionManager($this->name, $manager);
		if ($this->isDefault) {
			$service->setDefaultDatasource($this->name);
		}
		$this->connection = Propel::getConnectionDebug($this->name);
		return true;
	}

	/**
	 * Return Last Executed Query for Connection
	 * @return string
	 */
	public function getLastExecutedQuery() {
		return $this->connection->getLastExecutedQuery();
	}
}