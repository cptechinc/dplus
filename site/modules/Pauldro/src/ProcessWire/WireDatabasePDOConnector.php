<?php namespace Pauldro\ProcessWire;
// Base PHP
use PDOException;
// ProcessWire
use ProcessWire\Config;
use ProcessWire\WireData;
use ProcessWire\WireDatabasePDO;

/**
 * WireDatabasePDOConnector 
 * 
 * Wrapper class for creating a ProcessWire Database Connection
 * @property string  $wirename  Name to use to attach to wire()
 * @property Config  $dbconfig    DB Credentials
 * @property WireDatabasePDO $pdo  Connection
 */
class WireDatabasePDOConnector extends WireData {
	public function __construct() {
		$this->wirename = '';
		$this->dbconfig = false;
		$this->pdo      = false;
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
	 * Establish Connection
	 * @throws PDOException
	 * @return WireDatabasePDO
	 */
	public function connect() {
		if (empty($this->dbconfig)) {
			return false;
		}
		try {
			$this->pdo = WireDatabasePDO::getInstance($this->dbconfig);
		} catch (PDOException $e) {
			$this->logError($e->getMessage());
			$this->pdo = false;
			return false;
		}
		if (empty($this->wire("db-$this->wirename"))) {
			$this->wire("db-$this->wirename-pdo", $this->pdo, true);
		}
		return true;
	}

	/**
	 * Return Connection
	 * @return WireDatabasePDO
	 */
	public function connection() {
		if ($this->pdo instanceof WireDatabasePDO) {
			return $this->pdo;
		}
		$this->connect();
		return $this->pdo;
	}

/* =============================================================
	Logging
============================================================= */
	/**
	 * Writes Error Message to Database Error Log
	 * @param  string $message Error Message
	 * @return void
	 */
	protected function logError($message) {
		$date = date("Y-m-d h:m:s");
		$message = "[{$date}] [{$this->name}] $message";
		$this->wire('log')->save('db-errors', $message);
	}
}