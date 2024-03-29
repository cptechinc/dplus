<?php namespace Pauldro\ProcessWire;
// ProcessWire
use ProcessWire\WireData;

/**
 * File Hasher
 *
 * Returns File Hashed URLs for files under the $config->paths->templates directory
 * @property string $basepath  Base Path that file is in
 * @property string $baseurl   Base URL for file
 * @property string $hashtype  Hash Type to create hash for hash_file
 */
class FileHasher extends WireData {
	/** @var self */
	private static $instance;

	/**  @return self */
	public static function instance() {
		if (empty(self::$instance) === false) {
			return self::$instance;
		}
		self::$instance = new self();
		return self::$instance;
	}

	public function __construct() {
		$this->basepath = $this->wire('config')->paths->templates;
		$this->baseurl  = $this->wire('config')->urls->templates;
		$this->hashtype = $this->wire('config')->userAuthHashType;
	}

	/**
	 * Return Url to File with hash
	 * @param  string $file  File Path e.g. site/templates/styles/main.css | styles/main.css
	 * @return string
	 */
	public function getHashUrl($file) {
		$basefile = $this->stripPath($file);
		$filepath = $this->getFilePath($basefile);
		$hash = $this->getFileHash($filepath);
		return $this->baseurl."$basefile?v=$hash";
	}

	/**
	 * Remove Path From File Path
	 * EXAMPLE: site/templates/styles/main.css would return styles/main.css
	 * @param  string $file
	 * @return string
	 */
	public function stripPath($file) {
		return str_replace($this->filepath, '', $file);
	}

	/**
	 * Return File Path with new path
	 * EXAMPLE: styles/main.css would return site/templates/styles/main.css
	 * @param  string $basefile
	 * @return string
	 */
	public function getFilePath($basefile) {
		return $this->basepath . $basefile;
	}

	/**
	 * Return File Hash
	 * @param  string $file
	 * @return string
	 */
	public function getFileHash($file) {
		return hash_file($this->hashtype, $file);
	}
}
