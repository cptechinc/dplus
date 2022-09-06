<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use UnitofMeasureSaleQuery, UnitofMeasureSale;
use UnitofMeasurePurchaseQuery, UnitofMeasurePurchase;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the UMM code table
 */
class Umm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'UnitofMeasureSale';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_uom_sale';
	const DESCRIPTION        = 'Unit of Measure Code';
	const DESCRIPTION_RECORD = 'Unit of Measure Code';
	const RESPONSE_TEMPLATE  = 'Unit of Measure Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'umm';
	const DPLUS_TABLE           = 'UMM';
	const FIELD_ATTRIBUTES = [
		'code'          => ['type' => 'text', 'maxlength' => UnitofMeasureSale::MAX_LENGTH_CODE],
		'description'   => ['type' => 'text', 'maxlength' => 20],
		'conversion'    => ['type' => 'number', 'precision' => 5, 'max' => 9999999.00000],
		'stockbycase' => ['type' => 'text', 'default' => 'N'],
		'pricebyweight' => ['type' => 'text', 'default' => 'N'],
	];

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'code'          => $code->code,
			'description'   => $code->description,
			'conversion'    => $code->conversion,
			'stockbycase' => $code->stockbycase,
			'pricebyweight' => $code->pricebyweight,
		];
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Work Center Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(UnitofMeasureSale::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Code from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputUpdate(WireInput $input) {
		$success = parent::inputUpdate($input);
		if ($success === false) {
			return $success;
		}

		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
		$invalidfields = [];

		$code = $this->code($id);
		if (empty($code)) {
			return false;
		}
		return $this->copyCodeToUomPurchase($code);
	}

	/**
	 * Delete Code
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
		$code   = $this->code($id);

		$success = parent::inputDelete($input);
		if ($success === false) {
			return $success;
		}

		if (empty($code)) {
			return false;
		}
		return $this->copyCodeToUomPurchase($code);
	}

	/**
	 * Copy / Delete Code to Unit of Measure Purchase table
	 * @param  UnitofMeasureSale $code
	 * @return bool
	 */
	private function copyCodeToUomPurchase(UnitofMeasureSale $code) {
		$q = UnitofMeasurePurchaseQuery::create()->filterByCode($code->id);

		if ($q->count()) {
			$uomPur = $q->findOne();
		} else {
			$uomPur = new UnitofMeasurePurchase();
		}

		$uomPur->setCode($code->id);
		$uomPur->fromArray($code->toArray());
		$uomPur->save();

		if ($code->isDeleted()) {
			return $uomPur->delete();
		}
		return $uomPur->save();
	}

	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Code      $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields     = parent::_inputUpdate($input, $code);
		$invalidfieldsUmm  = $this->inputUpdateUmm($input, $code);
		$invalidfields = array_merge($invalidfields, $invalidfieldsUmm);
		return $invalidfields;
	}

	/**
	 * Update UnitofMeasureSale fields
	 * @param  WireInput $input Input Data
	 * @param  UnitofMeasureSale  $code
	 * @return array
	 */
	private function inputUpdateUmm(WireInput $input, UnitofMeasureSale $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = [];

		$code->setConversion(
			$values->float('conversion', [
					'min' => 1,
					'max' => $this->fieldAttribute('conversion', 'max'),
					'precision' => $this->fieldAttribute('conversion', 'precision'),
				]
			)
		);
		$code->setPricebyweight($values->yn('pricebyweight'));
		$code->setStockbycase($values->yn('stockbycase'));
		return $invalidfields;
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return UnitofMeasureSale
	 */
	public function new($id = '') {
		$code = new UnitofMeasureSale();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		$code->setConversion(1.00000);
		$code->setPricebyweight($this->fieldAttribute('pricebyweight', 'default'));
		$code->setStockbycase($this->fieldAttribute('stockbycase', 'default'));
		return $code;
	}
}
