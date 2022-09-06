<?php namespace Dplus\Codes\Mpm;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use PrWorkCenterQuery, PrWorkCenter;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the DCM code table
 */
class Dcm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'PrWorkCenter';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'po_confirm_code';
	const DESCRIPTION        = 'Work Center Code';
	const DESCRIPTION_RECORD = 'Work Center Code';
	const RESPONSE_TEMPLATE  = 'Work Center Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'dcm';
	const DPLUS_TABLE           = 'DCM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => PrWorkCenter::CODELENGTH],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Work Center Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(PrWorkCenter::aliasproperty('id'));
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

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return PrWorkCenter
	 */
	public function new($id = '') {
		$code = new PrWorkCenter();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
