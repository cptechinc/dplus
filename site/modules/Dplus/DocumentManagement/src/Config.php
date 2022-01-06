<?php namespace Dplus\DocManagement;
// ProcessWire
use ProcessWire\WireData;
// Dplus Document Management
use Dplus\DocManagement\Config;

/**
 * Document Viewer Config
 * Holds Config Values for the Viewer using docvwr
 */
class Config extends WireData {
	const CONFIGFILE = 'config.json';
	protected static $instance;

	public function __construct() {
		$this->folder = null;
		$this->viewer = null;
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
		$this->initConfigs();
	}

	public function initConfigs() {
		$json = $this->getConfigJson();
		$this->viewer = Config\Viewer::getInstance($json);
		$this->folder = Config\Folder::getInstance($json);
	}

	public function getConfigFilePath() {
		return $this->wire('config')->paths->siteModules . 'Dplus/DocumentManagement/config/' . self::CONFIGFILE;
	}

	public function getConfigJson() {
		return json_decode(file_get_contents($this->getConfigFilePath()), true);
	}
}
