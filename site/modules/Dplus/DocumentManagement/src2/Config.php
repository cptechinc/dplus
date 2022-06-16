<?php namespace Dplus\Docm;
// ProcessWire
use ProcessWire\Config as Base;
use ProcessWire\WireData;

/**
 * Config
 * 
 * Handles Dplus Document Management configuration data
 */
class Config extends Base {
	private static $instance;

	/**
	 * Return Instance
	 * @return self
	 */
	public static function instance() {
		if (empty(static::$instance)) {
			$instance = new static();
			$instance->initFromEnv();
			static::$instance = $instance;
		}
		return static::$instance;
	}

	public function __construct() {
		$this->folderUseLowerCase = false;
		$this->directories = new WireData();
		$this->directories->docvwr = '';

		$this->urls = new WireData();
		$this->urls->docvwr = '';
	}

	/**
	 * Load Config values from Environment
	 * @return void
	 */
	private function initFromEnv() {
		$this->folderUseLowerCase  = $_ENV['FOLDER_USE_LOWERCASE'] == 'true';
		$this->directories->docvwr = $_ENV['VIEWER_DIRECTORY'];
		$this->urls->docvwr        = $_ENV['URLPATH'];
	}
}