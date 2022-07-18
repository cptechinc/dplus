<?php namespace Dplus\Databases\Connectors;
// ProcessWire
use ProcessWire\Config;

class Dplus extends AbstractDatabaseConnector {
	const NAME_PROPEL = 'default';
	const NAME_PW     = 'dplus';
	const PROPEL_DEFAULT = true;

	protected static $instance;

/* =============================================================
	DB Credentials
============================================================= */
	/**
	 * Returns Config to connect to Database
	 * @return Config
	 */
	protected function dbconfig() {
		$config = $this->wire('config');

		if ($config->has('databases') === false) {
			$this->error("Credentials Not Found");
			return false;
		}

		if ($config->databases->has('dplus') === false) {
			$this->error("Credentials Not Found");
			return false;
		}
		return $config->databases->dplus;
	}

/* =============================================================
	DB Connect
============================================================= */
	
/* =============================================================
	Database Accessors
============================================================= */
	

/* =============================================================
	Logging
============================================================= */

}