<?php namespace Dplus\Min\Inmain;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\WireUpload;

use Dplus\DocManagement\Uploader\It\Itmimg as Uploader;
use Dplus\DocManagement\Updater\It\Itmimg  as Updater;

use Dplus\Codes\Response;

/**
 * Img
 * Class for uploading Images and tying them to Item IDs
 *
 * @property bool useAutofile Use Dplus Autofiler (otherwise use request methods)
 */
class Itmimg extends WireData {
	const FIELDS = [
		'image'      => ['type' => 'file', 'extensions' => ['jpeg', 'jpg', 'gif', 'png']],
		'itemID'     => ['type' => 'text', 'uppercase' => true],
	];

	private static $instance;

	public function __construct() {
		$this->sessionID   = session_id();
		$this->useAutofile = true;
	}

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Set the useAutofile Value
	 * @param  bool   $use Use Autofile?
	 * @return void
	 */
	public function useAutoFile(bool $use = false) {
		$this->useAutofile = $use;
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
	Input Processing Functions
============================================================= */
	/**
	 * Process Input, and take action
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	public function process(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'update':
				return $this->inputUpdate($input);
				break;
			case 'delete':
				return $this->inputDelete($input);
				break;
		}
		return false;
	}

	/**
	 * Update Image for File based on Input
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	public function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->offsetExists('itemID') === false) {
			$this->setResponse(Response::responseError('Item ID was not provided'));
			return false;
		}

		if (empty($_FILES)) {
			$this->setResponse(Response::responseError('Image was not uploaded'));
			return false;
		}

		return $this->upload($input);
	}

	/**
	 * Upload Image to directory
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function upload(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->text('itemID');

		$uploader = Uploader::getInstance();
		$uploader->useAutoFile($this->useAutofile);
		$uploader->inputName = 'image';
		$uploader->setFile($_FILES['image']);
		$uploader->setItemID($itemID);
		$success = $uploader->upload();

		if ($success === false) {
			$this->setResponse(Response::responseError("Image for $itemID was not uploaded"));
			return false;
		}

		if ($this->useAutofile === false) {
			$this->requestFileUpdate($input, $uploader);
		}

		$response = Response::responseSuccess("Uploaded $itemID Image");
		$response->setKey($values->text('itemID'));
		$this->setResponse($response);

		return true;
	}

	/**
	 * Return File Uploader with settings
	 * @param  array  $file          Element from $_FILES
	 * @param  string $basefilename  Filename without extension
	 * @return WireUpload
	 */
	private function getUploader(array $file, $basefilename) {
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

		$uploader = new WireUpload('image');
		$uploader->setMaxFiles(0);
		$uploader->setOverwrite(true);
		$uploader->setValidExtensions($this->fieldAttribute('image', 'extensions'));
		$uploader->setDestinationPath('/tmp/');
		$uploader->setTargetFilename($basefilename . ".$ext");
		return $uploader;
	}

	/**
	 * Make File Update Request
	 * @param  WireInput $input    Input Data
	 * @param  Uploader  $uploader File Uploader
	 * @return bool
	 */
	private function requestFileUpdate(WireInput $input, Uploader $uploader) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->text('$itemID');

		$updater = new Updater();
		$updater->directory = $uploader::UPLOAD_DIR;
		$updater->filelocation  = $uploader->filelocation;
		$updater->$itemID = $itemID;
		return $updater->update();
	}

/* =============================================================
	Response Functions
============================================================= */
	/**
	 * Set Session Response
	 * @param Response $response
	 */
	public function setResponse(Response $response) {
		$this->wire('session')->setFor('response', 'itm-img', $response);
	}

	/**
	 * Return Session Response
	 * @return Response
	 */
	public function getResponse() {
		return $this->wire('session')->getFor('response', 'itm-img');
	}

	/**
	 * Delete Session Response
	 */
	public function deleteResponse() {
		$this->wire('session')->removeFor('response', 'itm-img');
	}
}
