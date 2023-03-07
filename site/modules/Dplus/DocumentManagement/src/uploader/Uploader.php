<?php namespace Dplus\DocManagement;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\WireUpload;

use Dplus\Configs;

/**
 * Uploader
 * Base class for Uploading Files for Document Management
 *
 * @property string $inputName     Input Name
 * @property array  $file          $_FILES element
 * @property string $filelocation  Final File Location
 */
class Uploader extends WireData {
	const UPLOAD_DIR = '/tmp/';
	const FIELDS = [];
	const FILENAME_PREFIX = '';
	const REPLACE_IN_FILENAME = [
		' ' => '^',
		'-' => '~',
		'/' => '{'
	];

	private static $instance;

	public function __construct() {
		$this->inputName = '';
		$this->file = [];
		$this->uploadDirectory = static::UPLOAD_DIR;
		$this->filelocation  = '';
	}

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Setters
============================================================= */
	/**
	 * Set $_FILES element
	 * @param array $file  from $_FILES
	 */
	public function setFile(array $file) {
		$this->file = $file;
	}

	/**
	 * Use Autofile
	 * @param  bool   $autofile Use Autofile
	 * @return void
	 */
	public function useAutoFile(bool $autofile) {
		if ($autofile) {
			$sysd = Configs\Sysd::config();
			$this->uploadDirectory = $sysd->dirautofile . '/';
			if ($this->config->company == 'ugm') {
				$this->uploadDirectory = $sysd->dircerts . '/';
			}
			return true;
		}
		$this->uploadDirectory = static::UPLOAD_DIR;
		return true;
	}

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Return Field Attribute value
	 * @param  string $field Field Name
	 * @param  string $attr  Attribute Name
	 * @return mixed|bool
	 */
	public function fieldAttribute($field = '', $attr = '') {
		if (empty($field) || empty($attr)) {
			return false;
		}
		if (array_key_exists($field, static::FIELDS) === false) {
			return false;
		}
		if (array_key_exists($attr, static::FIELDS[$field]) === false) {
			return false;
		}
		return static::FIELDS[$field][$attr];
	}

/* =============================================================
	FILE Naming
============================================================= */
	/**
	 * Return File name sanitized for use
	 * @param  string $filename file Name
	 * @return string
	 */
	public function getAcceptableFilename($filename) {
		return str_replace(array_keys(self::REPLACE_IN_FILENAME), array_values(self::REPLACE_IN_FILENAME), $filename);
	}

	/**
	 * Return Target File name
	 * @param  array  $file      Node from $_FILES
	 * @param string $filename  File Name
	 * @return string
	 */
	public function getTargetFilename(array $file, $filename) {
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		return $this->getFilenamePrefix(). $this->getAcceptableFilename($filename) . ".$ext";
	}

	/**
	 * Return File Name Prefix
	 * @return string
	 */
	public function getFilenamePrefix() {
		$config = Config::getInstance();
		return $config->folder->useLowercase() ? strtolower(static::FILENAME_PREFIX) : static::FILENAME_PREFIX;
	}



/* =============================================================
	FILE Uploading
============================================================= */
	/**
	 * Upload Image to directory
	 * @return bool
	 */
	public function upload() {
		$uploader = $this->getUploader($this->file);
		$files    = $uploader->execute();

		if (empty($files)) {
			return false;
		}
		$this->filelocation = $this->uploadDirectory . $files[0];
		return true;
	}

	/**
	 * Return File Uploader with settings
	 * @param  array  $file          Element from $_FILES
	 * @return WireUpload
	 */
	protected function getUploader(array $file) {
		$uploader = new WireUpload($this->inputName);
		$uploader->setMaxFiles(1);
		$uploader->setOverwrite(true);
		$uploader->setValidExtensions($this->fieldAttribute($this->inputName, 'extensions'));
		$uploader->setDestinationPath($this->uploadDirectory);
		return $uploader;
	}
}
