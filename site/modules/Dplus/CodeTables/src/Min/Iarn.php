<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Models
use InvAdjustmentReason;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Response;
use Dplus\Codes\AbstractCodeTableSimpleEditable;

/**
 * Class that handles the CRUD of the IARN code table
 */
class Iarn extends AbstractCodeTableSimpleEditable {
	const MODEL              = 'InvAdjustmentReason';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'inv_iarn_code';
	const DESCRIPTION        = 'Inventory Adjustment Reason';
	const DESCRIPTION_RECORD = 'Inventory Adjustment Reason';
	const RESPONSE_TEMPLATE  = 'Inventory Adjustment Reason {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'iarn';
	const DPLUS_TABLE           = 'IARN';
	const FIELD_ATTRIBUTES = [
		'code'             => ['type' => 'text', 'maxlength' => InvAdjustmentReason::MAX_LENGTH_CODE],
		'description'      => ['type' => 'text', 'maxlength' => 30],
		'sysdefined'       => ['type' => 'text', 'options' => ['Y' => 'Yes', 'N' => 'No']],
	];

	public function codeJson(Code $code) {
		$json = parent::codeJson($code);
		$json['sysdefined'] = $code->sysdefined;
		return $json;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return IDs
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(InvAdjustmentReason::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return new InvAdjustmentReason
	 * @param  string $id Code
	 * @return InvAdjustmentReason
	 */
	public function new($id = '') {
		$code = new InvAdjustmentReason();

		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		$code->setSysdefined('N');
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Delete Code
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);

		if ($this->exists($id) === false) {
			$response = Response::responseSuccess("Code $id was deleted");
			$response->buildMessage(static::RESPONSE_TEMPLATE);
			$response->setCode($id);
			return true;
		}
		/** @var InvAdjustmentReason */
		$code = $this->code($id);

		// Check if Reason is system defined
		if ($this->sanitizer->ynbool($code->sysdefined)) {
			$response = Response::responseError("Code $id cannot be deleted");
			$response->setCode($id);
			$this->setResponse($response);
			return false;
		}

		$code->delete();
		$response = $this->saveAndRespond($code);
		$this->setResponse($response);
		return $response->hasSuccess();
	}
}
