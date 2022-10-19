<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// Dplus Models
use Shipvia;
// ProcessWire
use ProcessWire\WireInput;
use ProcessWire\WireInputData;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;

/**
 * Class that handles the CRUD of the CSV code table
 * TODO: FINISH
 */
class Csv extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'Shipvia';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_svia';
	const DESCRIPTION        = 'Ship Via Code';
	const DESCRIPTION_RECORD = 'Ship Via Code';
	const RESPONSE_TEMPLATE  = 'Ship Via Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'csv';
	const DPLUS_TABLE           = 'CSV';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 8],
		'description' => ['type' => 'text', 'maxlength' => 20],
		'service'     => ['type' => 'text', 'maxlength' => 25],
		'billing'     => ['type' => 'text', 'options' => ['' => 'Not Used', 'F' => 'Freight Collect', 'B' => 'Bill to Third Party', 'C' => 'Consignee'], 'default' => ''],
		'residential' => ['type' => 'text', 'options' => ['' => 'Not Used', 'Y' => 'Yes', 'N' => 'No'], 'default' => ''],
		'priority'    => ['type' => 'text', 'maxlength' => 3],
		'shippingarea' => ['type' => 'text', 'maxlength' => 1],
		'airshipment'  => ['type' => 'text', 'options' => ['Y' => 'Yes', 'N' => 'No'], 'default' => 'N'],
		'commercialflight' => ['type' => 'text', 'options' => ['Y' => 'Yes', 'N' => 'No'], 'default' => 'N'],
		'scac'             => ['type' => 'text', 'maxlength' => 4],
		'edimethod'        => ['type' => 'text', 'maxlength' => 2],
		'chargefreight'    => ['type' => 'text', 'options' => ['Y' => 'Yes', 'N' => 'No'], 'default' => 'Y'],
		'useroute'         => ['type' => 'text', 'options' => ['Y' => 'Yes', 'N' => 'No'], 'default' => 'N'],
		'addsurcharge'     => ['type' => 'text', 'options' => ['Y' => 'Yes', 'N' => 'No'], 'default' => 'N'],
		'surchargepercent' => ['type' => 'number', 'max' => 99.999, 'precision' => 3, 'default' => 0.000],
		'artaxcode'        => ['type' => 'text', 'maxlength' => Tm::FIELD_ATTRIBUTES['code']['maxlength']],
	];

	/** @var self */
	protected static $instance;

	public function codeJson(Code $code) {
		$json = parent::codeJson($code);
		foreach (array_keys(static::FIELD_ATTRIBUTES) as $field) {
			$json[$field] = $code->$field;
		}
		return $json;
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return new Shipvia
	 * @param  string $id Code
	 * @return Shipvia
	 */
	public function new($id = '') {
		/** @var Shipvia */
		$code = parent::new($id);
		$code->setBilling($this->fieldAttribute('billing', 'default'));
		$code->setResidential($this->fieldAttribute('residential', 'default'));
		$code->setAirshipment($this->fieldAttribute('airshipment', 'default'));
		$code->setCommercialflight($this->fieldAttribute('commercialflight', 'default'));
		$code->setChargefreight($this->fieldAttribute('chargefreight', 'default'));
		$code->setUseroute($this->fieldAttribute('useroute', 'default'));
		$code->setAddsurcharge($this->fieldAttribute('addsurcharge', 'default'));
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput  $input Input Data
	 * @param  Shipvia    $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields  = parent::_inputUpdate($input, $code);
		$invalidfieldsBI = $this->_inputUpdateBillingResidential($values, $code);
		$invalidfieldsSPS = $this->_inputUpdateServicePriorityShipping($values, $code);
		$invalidfieldsAF = $this->_inputUpdateAirFlight($values, $code);
		$invalidfieldsSE = $this->_inputUpdateScacEdi($values, $code);
		$invalidfieldsFR = $this->_inputUpdateChargefreightRoute($values, $code);
		$invalidfieldsSU = $this->_inputUpdateSurcharge($values, $code);
		$invalidfieldsTX = $this->_inputUpdateArtaxcode($values, $code);

		$invalidfields = array_merge(
			$invalidfields, $invalidfieldsBI, $invalidfieldsSPS, 
			$invalidfieldsAF, $invalidfieldsSE, $invalidfieldsFR,
			$invalidfieldsSU, $invalidfieldsTX
		);
		return $invalidfields;
	}

	/**
	 * Update Billing, Residential fields
	 * @param  WireInputData $values
	 * @param  Shipvia       $code
	 * @return array
	 */
	private function _inputUpdateBillingResidential(WireInputData $values, Shipvia $code) {
		$code->setBilling($this->fieldAttribute('billing', 'default'));
		$code->setResidential($this->fieldAttribute('residential', 'default'));

		if (array_key_exists(strtoupper($values->text('residential')), self::FIELD_ATTRIBUTES['residential']['options'])) {
			$code->setResidential(strtoupper($values->text('residential')));
		}

		if (array_key_exists(strtoupper($values->text('billing')), self::FIELD_ATTRIBUTES['billing']['options'])) {
			$code->setBilling(strtoupper($values->text('billing')));
		}
		return [];
	}

	/**
	 * Update Service, Priority, Shipping Area
	 * @param  WireInputData $values
	 * @param  Shipvia       $code
	 * @return array
	 */
	private function _inputUpdateServicePriorityShipping(WireInputData $values, Shipvia $code) {
		$code->setService(strtoupper($values->text('service', ['maxLength' => $this->fieldAttribute('service', 'maxlength')])));
		$code->setPriority(strtoupper($values->text('priority', ['maxLength' => $this->fieldAttribute('priority', 'maxlength')])));
		$code->setShippingarea(strtoupper($values->text('shippingarea', ['maxLength' => $this->fieldAttribute('shippingarea', 'maxlength')])));
		return [];
	}

	/**
	 * Update Air Shipment, commercial flight fields
	 * @param  WireInputData $values
	 * @param  Shipvia       $code
	 * @return array
	 */
	private function _inputUpdateAirFlight(WireInputData $values, Shipvia $code) {
		$code->setAirshipment($this->fieldAttribute('airshipment', 'default'));
		$code->setCommercialflight($this->fieldAttribute('commercialflight', 'default'));

		if (array_key_exists(strtoupper($values->text('airshipment')), self::FIELD_ATTRIBUTES['airshipment']['options'])) {
			$code->setAirshipment(strtoupper($values->text('airshipment')));
		}

		if (array_key_exists(strtoupper($values->text('commercialflight')), self::FIELD_ATTRIBUTES['commercialflight']['options'])) {
			$code->setCommercialflight(strtoupper($values->text('commercialflight')));
		}
		return [];
	}

	/**
	 * Update Scac, Edimethod
	 * @param  WireInputData $values
	 * @param  Shipvia       $code
	 * @return array
	 */
	private function _inputUpdateScacEdi(WireInputData $values, Shipvia $code) {
		$code->setScac(strtoupper($values->text('scac', ['maxLength' => $this->fieldAttribute('scac', 'maxlength')])));
		$code->setEdimethod(strtoupper($values->text('edimethod', ['maxLength' => $this->fieldAttribute('edimethod', 'maxlength')])));
		return [];
	}

	/**
	 * Update Charge Freight, Use Route fields
	 * @param  WireInputData $values
	 * @param  Shipvia       $code
	 * @return array
	 */
	private function _inputUpdateChargefreightRoute(WireInputData $values, Shipvia $code) {
		$code->setChargefreight($this->fieldAttribute('chargefreight', 'default'));
		$code->setUseroute($this->fieldAttribute('useroute', 'default'));

		if (array_key_exists(strtoupper($values->text('chargefreight')), self::FIELD_ATTRIBUTES['chargefreight']['options'])) {
			$code->setChargefreight(strtoupper($values->text('chargefreight')));
		}

		if (array_key_exists(strtoupper($values->text('useroute')), self::FIELD_ATTRIBUTES['useroute']['options'])) {
			$code->setUseroute(strtoupper($values->text('useroute')));
		}
		return [];
	}

	/**
	 * Update Surcharge fields
	 * @param  WireInputData $values
	 * @param  Shipvia       $code
	 * @return array
	 */
	private function _inputUpdateSurcharge(WireInputData $values, Shipvia $code) {
		$code->setAddsurcharge($this->fieldAttribute('addsurcharge', 'default'));
		$code->setSurchagepercent($this->fieldAttribute('surchargepct', 'default'));

		if ($values->ynbool('addsurgcharge') === false) {
			return [];
		}
		$code->setAddsurcharge($values->yn('addsurcharge'));
		$options = [
			'precision' => $this->fieldAttribute('surchargepct', 'precision'),
			'max'       => $this->fieldAttribute('surchargepct', 'max'),
		];
		$code->setSurchagepercent($values->float('surchargepct', $options));
		return [];
	}

	/**
	 * Update taxcode field
	 * @param  WireInputData $values
	 * @param  Shipvia       $code
	 * @return array
	 */
	private function _inputUpdateArtaxcode(WireInputData $values, Shipvia $code) {
		$original = $code->artaxcode;

		if (Tm::instance()->exists($values->text('artaxcode'))) {
			$code->setArtaxcode($values->text('artaxcode'));
		}
		return [];
	}
}
