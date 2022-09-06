<?php namespace Dplus\Codes\Min;
// Propel Classes
// use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// Dplus Models
use CustomerStockingCell;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;

/**
 * Class that handles the CRUD of the CSCCM code table
 */
class Csccm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'CustomerStockingCell';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_cell_code';
	const DESCRIPTION        = 'Customer Stocking Cell';
	const DESCRIPTION_RECORD = 'Customer Stocking Cell';
	const RESPONSE_TEMPLATE  = 'Customer Stocking Cell {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'csccm';
	const DPLUS_TABLE           = 'CSCCM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => CustomerStockingCell::MAX_LENGTH_CODE],
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
		$q->select(CustomerStockingCell::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return CustomerStockingCell
	 */
	public function new($id = '') {
		$code = new CustomerStockingCell();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
