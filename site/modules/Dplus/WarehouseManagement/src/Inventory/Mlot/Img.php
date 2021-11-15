<?php namespace Dplus\Wm\Inventory\Mlot;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\WireUpload;

use Dplus\DocManagement\Uploader\Lt\Lotimg as Uploader;

use Dplus\Codes\Response;

/**
 * Img
 * Class for uploading Images and tying them to lots
 */
class Img extends WireData {
	const FIELDS = [
		'image'      => ['type' => 'file', 'extensions' => ['jpeg', 'jpg', 'gif', 'png']],
		'lotserial'  => ['type' => 'text', 'uppercase' => true],
	];

	private static $instance;

	public function __construct() {
		$this->sessionID = session_id();
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

		if ($values->offsetExists('lotserial') === false) {
			$this->setResponse(Response::responseError('Lot / Serial Number was not provided'));
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
		$lotserial = $values->text('lotserial');

		$uploader = Uploader::getInstance();
		$uploader->inputName = 'image';
		$uploader->setFile($_FILES['image']);
		$uploader->setLotserial($lotserial);
		$success = $uploader->upload();

		if ($success === false) {
			$this->setResponse(Response::responseError("Image for $lotserial was not uploaded"));
			return false;
		}

		$response = Response::responseSuccess("Uploaded $lotserial Image");
		$response->setKey($values->text('lotserial'));
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

/* =============================================================
	Response Functions
============================================================= */
	/**
	 * Set Session Response
	 * @param Response $response
	 */
	public function setResponse(Response $response) {
		$this->wire('session')->setFor('response', 'mlot-img', $response);
	}

	/**
	 * Return Session Response
	 * @return Response
	 */
	public function getResponse() {
		return $this->wire('session')->getFor('response', 'mlot-img');
	}

	/**
	 * Delete Session Response
	 */
	public function deleteResponse() {
		$this->wire('session')->removeFor('response', 'mlot-img');
	}
}
