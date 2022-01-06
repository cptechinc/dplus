<?php namespace Dplus\DocManagement\Config;
// Purl URI manipulation library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;

/**
 * Document Viewer Config
 */
class Viewer extends Config {
	protected static $instance;

	public static function getInstance($json = []) {
		if (empty(self::$instance)) {
			$instance = new self();
			$instance->initJson($json);
			self::$instance = $instance;
		}
		return self::$instance;
	}

	public function __construct() {
		$this->directory = '';
		$this->urlpath   = '';
		$this->url       = '';
	}

	public function initConfig($json = []) {
		$config = $json['viewer'];
		$this->setArray($config);
	}
}
