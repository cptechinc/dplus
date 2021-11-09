<?php namespace Dplus\DocManagement\Viewer;

use ProcessWire\WireData;

/**
 * Document Viewer Config
 * Holds Config Values for the Viewer using docvwr
 */
class Config extends WireData {
	const CONFIGFILE = 'config.json';
	private static $instance;

	public function __construct() {
		$this->directory = '';
		$this->urlpath   = '';
		$this->url       = '';
	}

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			$instance->init();
			self::$instance = $instance;
		}
		return self::$instance;
	}

	public function init() {
		$this->initConfig();
	}

	public function initConfig() {
		$json = json_decode(file_get_contents($this->getConfigFilePath()), true);
		$config = $json['viewer'];
		$this->directory = $config['directory'];
		$this->urlpath   = $config['urlpath'];
		$this->url       = $config['url'];
	}

	public function getConfigFilePath() {
		return $this->wire('config')->paths->siteModules . 'Dplus/DocumentManagement/config/' . self::CONFIGFILE;
	}
}
