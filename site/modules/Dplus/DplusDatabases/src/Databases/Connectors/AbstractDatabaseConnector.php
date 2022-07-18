<?php namespace Dplus\Databases\Connectors;
// ProcessWire
use ProcessWire\Config;
use ProcessWire\WireData;
use ProcessWire\WireDatabasePDO;
// Pauldro ProcessWire
use Pauldro\ProcessWire\WireDatabasePDOConnector;

/**
 * Base
 * 
 * Service for Connecting MySQL database with ProcessWire / Propel ORM
 * @property Config                    $dbconfig
 * @property Propel\Connection         $propel Propel Connection
 * @property WireDatabasePDOConnector  $pw
 */
abstract class AbstractDatabaseConnector extends WireData {
	const NAME_PROPEL = '';
	const NAME_PW	  = '';
	const PROPEL_DEFAULT = false;

	protected static $instance;

	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	public function __construct() {
		$this->dbconfig = $this->dbconfig();
		$this->propel	= new Propel\Connection();
		$this->pw       = new WireDatabasePDOConnector();
	}

/* =============================================================
	DB Credentials
============================================================= */
	/**
	 * Returns Config to connect to Database
	 * @return Config
	 */
	abstract protected function dbconfig();

/* =============================================================
	DB Connect
============================================================= */
	public function connect() {
		if ($this->connectProcessWire() === false) {
			return false;
		}

		if ($this->connectPropel() === false) {
			return false;
		}
		return true;
	}

	/**
	 * Return if Connection was made via ProcessWire
	 * @return bool
	 */
	public function connectProcessWire() {
		$this->pw->wirename = static::NAME_PW;
		$this->pw->setDbconfig($this->dbconfig);
		return $this->pw->connect();
	}

	/**
	 * Makes Propel Connection to database
	 * @return bool
	 */
	public function connectPropel() {
		if (empty($this->dbconfig)) {
			return false;
		}
		if ($this->propel->connection) {
			return true;
		}
		$this->propel->name = static::NAME_PROPEL;
		$this->propel->setIsDefault(static::PROPEL_DEFAULT);
		$this->propel->setDbconfig($this->dbconfig);
		return $this->propel->connect();
	}

/* =============================================================
	Database Accessors
============================================================= */
	/**
	 * Return WireDatabasePDO
	 * @return WireDatabasePDO
	 */
	public function getWireDatabasePDO() {
		if ($this->pw->pdo) {
			return $this->pw->pdo;
		}
		$this->connectProcessWire();
		return $this->pw->pdo;
	}

	public function getPropelConnection() {
		return $this->propel->connection;
	}

	public function getLastExecutedQuery() {
		return $this->propel->getLastExecutedQuery();
	}

/* =============================================================
	Logging
============================================================= */
	/**
	 * Writes Error Message to Database Error Log
	 * @param  string $message Error Message
	 * @return void
	 */
	public function logError($message) {
		$date = date("Y-m-d h:m:s");
		$class = static::NAME_PW;
		$message = "[{$date}] [{$class}] $message";
		$this->wire('log')->save('db-errors', $message);
	}

}