<?php namespace Dplus\DocManagement;

use ProcessWire\WireData;

/**
 * Copier
 * Copies documents
 */
class Copier extends WireData {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}
	
	public function __construct() {
		$this->directory = $this->wire('config')->directory_webdocs;
	}

	public function useDocVwrDirectory() {
		$this->directory = $this->wire('config')->directoryDocVwr;
	}

	public function useOrderfilesDirectory() {
		$this->directory = $this->wire('config')->directory_webdocs;
	}

/* =============================================================
	Get Functions
============================================================= */
	/**
	 * Return if File Exists in Directory
	 * @param  string $dir      Directory
	 * @param  string $filename File Name
	 * @return bool
	 */
	public function exists($dir, $filename) {
		return file_exists("$dir/$filename");
	}

	/**
	 * Returns if file is already in the web access directory
	 * @param  string $filename File Name
	 * @return bool             Is the file in the web access directory?
	 */
	public function isInDirectory($filename) {
		return $this->exists($this->directory, $filename);
	}

/* =============================================================
	File Retrieval Functions
============================================================= */
	/**
	 * Copies a file from a directory into the destination directory
	 * @param  string $directory   Directory which the file resides
	 * @param  string $filename    File Name
	 * @param  string $destination Destination Directory
	 * @return bool                Was file copied to the new directory
	 */
	public function copyFile($directory, $filename, $destination = '') {
		if ($this->exists($directory, $filename) === false) {
			return false;
		}
		$destination = empty($destination) ? $this->directory : $destination;
		if (file_exists($destination) === false) {
			return false;
		}
		$srcfile = "$directory/$filename";
		$newfile = "$destination/$filename";
		return copy($srcfile, $newfile);
	}
}
