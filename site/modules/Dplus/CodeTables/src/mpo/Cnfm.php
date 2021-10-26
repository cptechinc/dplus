<?php namespace Dplus\Codes\Mpo;
// Purl URI Library
use Purl\Url;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;
// Dplus Models
use PoConfirmCodeQuery, PoConfirmCode;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Codes
use Dplus\Codes\Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the CNFM code table
 */
class Cnfm extends Base {
	const MODEL              = 'PoConfirmCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'po_confirm_code';
	const DESCRIPTION        = 'PO Confirmation Code';
	const DESCRIPTION_RECORD = 'PO Confirmation Code';
	const RESPONSE_TEMPLATE  = 'PO Confirmation Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'cnfm';
	const DPLUS_TABLE           = 'CNFM';

	protected static $instance;

	/**
	 * Return the Max Length of characters for the code
	 * NOTE: Used for the JS
	 * @return int
	 */
	public function codeMaxLength() {
		return PoConfirmCode::MAX_LENGTH_CODE;
	}

	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 4],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

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
		if (array_key_exists($field, self::FIELD_ATTRIBUTES) === false) {
			return false;
		}
		if (array_key_exists($attr, self::FIELD_ATTRIBUTES[$field]) === false) {
			return false;
		}
		return self::FIELD_ATTRIBUTES[$field][$attr];
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Purchase Order Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(PoConfirmCode::get_aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return the Code records from Database
	 * @return ObjectCollection
	 */
	public function codes() {
		$q = $this->getQueryClass();
		return $q->find();
	}

	/**
	 * Return the Code records from Database filtered by ProductLne ID
	 * @param  string $id
	 * @return PoConfirmCode
	 */
	public function code($id) {
		$q = $this->query();
		return $q->findOneById($id);
	}

	/**
	 * Returns if Code Exists
	 * @param  string $id
	 * @return bool
	 */
	public function exists($id) {
		$q = $this->query();
		return boolval($q->filterById($id)->count());
	}

	/**
	 * Return New or Existing PO Confirm Code
	 * @param  string $id  Code ID
	 * @return PoConfirmCode
	 */
	public function getOrCreate($id = '') {
		if ($this->exists($id)) {
			return $this->code($id);
		}
		$code = new PoConfirmCode();
		if (strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'delete-code':
				$this->inputDelete($input);
				break;
			case 'edit-code':
				$this->inputUpdate($input);
				break;
		}
	}

	/**
	 * Update CNFM Code from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);

		$code = $this->getOrCreate($id);
		$code->setDescription($values->text('description', ['maxLength' => $this->fieldAttribute('description', 'maxlength')]));
		$code->setDate(date('Ymd'));
		$code->setTime(date('His'));
		$response = $this->saveAndRespond($code);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Delete CNFM Code
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);

		if ($this->exists($id) === false) {
			$response = Response::responseSuccess("PO Confirmation Code $id was deleted");
			$response->setCode($id);
			return true;
		}
		$code = $this->code($id);
		$code->delete();
		$response = $this->saveAndRespond($code);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

/* =============================================================
	CRUD Response
============================================================= */
	/**
	 * Return Response based on the outcome of the database save
	 * @param  PoConfirmCode $code  PO Confirmation Code
	 * @return Response
	 */
	protected function saveAndRespond(PoConfirmCode $code) {
		$is_new = $code->isDeleted() ? false : $code->isNew();
		$saved  = $code->isDeleted() ? $code->isDeleted() : $code->save();

		$response = new Response();
		$response->setCode($code->id);

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($code->isDeleted()) {
			$response->setAction(Response::CRUD_DELETE);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}

		$response->buildMessage(self::RESPONSE_TEMPLATE);
		if ($response->hasSuccess()) {
			$this->updateDplus($code->id);
		}
		return $response;
	}

	/**
	 * Set Session Response
	 * @param Response $response
	 */
	public function setResponse(Response $response) {
		$this->wire('session')->setFor('response', self::RECORDLOCKER_FUNCTION, $response);
	}

	/**
	 * Return Session Response
	 * @return Response
	 */
	public function getResponse() {
		return $this->wire('session')->getFor('response', self::RECORDLOCKER_FUNCTION);
	}

	/**
	 * Delete Session Response
	 */
	public function deleteResponse() {
		$this->wire('session')->removeFor('response', self::RECORDLOCKER_FUNCTION);
	}
}
