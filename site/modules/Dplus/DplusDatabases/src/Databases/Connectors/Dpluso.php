<?php namespace Dplus\Databases\Connectors;
// ProcessWire
use ProcessWire\Config;

class Dpluso extends AbstractDatabaseConnector  {
	const NAME_PROPEL = 'dplusodb';
	const NAME_PW     = 'dpluso';
	const PROPEL_DEFAULT = false;

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

		if ($config->databases->has('dpluso') === false) {
			$this->error("Credentials Not Found");
			return false;
		}
		return $config->databases->dpluso;
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