<?php namespace Dplus\DocManagement\Uploader\Lt;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\WireUpload;
// Dplus Document Management
use Dplus\DocManagement\Uploader as Base;
use Dplus\DocManagement\Config;

/**
 * Lotimg
 * Class for uploading Images and tying them to lots
 *
 * @property string $lotserial       Lot / Serial Number
 */
class Lotimg extends Base {
	const FILENAME_PREFIX = 'LOTIMG_';
	const FIELDS = [
		'image'      => ['type' => 'file', 'extensions' => ['jpeg', 'jpg', 'gif', 'png']],
		'lotserial'  => ['type' => 'text', 'uppercase' => true],
	];

	private static $instance;

	public function __construct() {
		parent::__construct();
		$this->lotserial = '';
	}

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

	/**
	 * Set Lotserial
	 * @param string $lotserial  Lot / Serial Number
	 */
	public function setLotserial($lotserial) {
		$this->lotserial = strtoupper($this->wire('sanitizer')->text($lotserial));
	}

	/**
	 * Return File Name Prefix
	 * @return string
	 */
	public function getFilenamePrefix() {
		$config = Config::getInstance();
		return $config->folder->useLowercase() ? strtolower(self::FILENAME_PREFIX) : self::FILENAME_PREFIX;
	}

/* =============================================================
	FILE Uploading
============================================================= */
	/**
	 * Return File Uploader with settings
	 * @param  array  $file          Element from $_FILES
	 * @return WireUpload
	 */
	protected function getUploader(array $file) {
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

		$uploader = parent::getUploader($file);
		$uploader->setTargetFilename(self::FILENAME_PREFIX . $this->lotserial . ".$ext");
		$uploader->setLowercase(false);
		return $uploader;
	}
}
