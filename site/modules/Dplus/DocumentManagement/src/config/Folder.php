<?php namespace Dplus\DocManagement\Config;
// Purl URI manipulation library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;


/**
 * Document Folder Config
 */
class Folder extends Config {
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
		$this->useLowerCase = true;
	}

	public function initConfig($json = []) {
		$config = $json['folder'];
		$this->setArray($config);
	}

	public function useLowercase() {
		return boolval($this->useLowerCase);
	}
}
