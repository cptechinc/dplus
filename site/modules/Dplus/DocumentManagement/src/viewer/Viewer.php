<?php namespace Dplus\DocManagement;
// Purl URI manipulation library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;
// Dplus Document Management
use Dplus\DocManagement\Viewer\Config;


/**
 * Document Viewer
 *
 * Decorator for DocumentQuery to find Documents in Database
 */
class Viewer extends WireData {
	private static $instance;

	public function __construct() {
		$this->configs = Config::getInstance();
	}

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Viewer Functions
============================================================= */
	/**
	 * Return if File Exists
	 * @param  string $file  File Name
	 * @return bool
	 */
	public function exists($file) {
		return file_exists($this->configs->directory . '/' . $file);
	}

	/**
	 * Return URL to file
	 * @param  string $file  File Name
	 * @return string
	 */
	public function url($file) {
		return $this->buildUrl($file);
	}

	/**
	 * Return URL to file based on config
	 * @param  string $file  File Name
	 * @return string
	 */
	private function buildUrl($file) {
		$url = new Purl($this->wire('pages')->get('/')->url);
		$url->path = $this->configs->urlpath;

		if (empty($this->configs->url) === false) {
			$url = new Purl($this->configs->url);
		}

		return $url->getUrl() . $file;
	}

}