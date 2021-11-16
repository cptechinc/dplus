<?php namespace Dplus\DocManagement;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\WireUpload;

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

	private static $instance;

	public function __construct() {
		$this->inputName = '';
		$this->file = [];
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
		$this->filelocation = static::UPLOAD_DIR . $files[0];
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
		$uploader->setDestinationPath(static::UPLOAD_DIR);
		return $uploader;
	}
}
