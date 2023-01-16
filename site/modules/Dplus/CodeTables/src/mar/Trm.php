<?php namespace Dplus\Codes\Mar;
// Propel ORM Library
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// Dplus Models
use ArTermsCode;
// ProcessWire
use ProcessWire\WireInput;
use ProcessWire\WireInputData;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;

/**
 * Class that handles the CRUD of the TRM code table
 */
class Trm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'ArTermsCode';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'ar_term_code';
	const DESCRIPTION        = 'Customer Terms Code';
	const DESCRIPTION_RECORD = 'Customer Terms Code';
	const RESPONSE_TEMPLATE  = 'Customer Terms Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'trm';
	const DPLUS_TABLE           = 'TRM';
	const FIELD_ATTRIBUTES = [
		'code'         => ['type' => 'text', 'maxlength' => 4],
		'description'  => ['type' => 'text', 'maxlength' => 20],
		'method'       => ['type' => 'text', 'default' => 'S', 'options' => ['S' => 'Standard Terms', 'E' => 'End of Month Terms']],
		'type'         => ['type' => 'text', 'default' => 'STD', 'optionsS' => ['STD' => 'Standard', 'CC' => 'Credit Card', 'CA' => 'Cash', 'COD' => 'COD', 'CDC' => 'COD Cash'], 'optionsE' => ['STD' => 'Standard', 'CA' => 'Cash', 'COD' => 'COD', 'CDC' => 'COD Cash' ]],
		'hold'         => ['type' => 'text', 'default' => 'N', 'options' => ['Y' => 'Yes', 'N' => 'No']],
		'expiredate'   => ['type' => 'text', 'default' => '', 'inputFormat' => 'm/d/Y', 'recordFormat' => 'Ymd'],
		'country'      => ['type' => 'text', 'default' => ''],
		'ccprefix'     => ['type' => 'text', 'default' => ''],
		'freightallow' => ['type' => 'text', 'default' => 'N', 'options' => ['Y' => 'Yes', 'N' => 'No']],

		// THESE ARE FOR THE SPLITS, USE BASE NAME
		'eom_disc_percent' => ['type' => 'number', 'max' => 99.99, 'precision' => 2],
		'eom_disc_day'    => ['type' => 'number', 'max' => 31],
		'eom_disc_months' => ['type' => 'number', 'max' => 99],
		'eom_due_day'     => ['type' => 'number', 'max' => 31, 'min' => 1],
		'eom_plus_months'  => ['type' => 'number', 'max' => 99],
		'eom_from_day'     => ['type' => 'number', 'max' => 98],
		'eom_thru_day'     => ['type' => 'number', 'max' => 99, 'defaultToMaxAt' => 28],

		'eom_from_day1'     => ['type' => 'number',  'default' => 1],
		'eom_thru_day1'     => ['type' => 'number', 'max' => 99, 'default' => 99],

		'std_disc_date'    => ['type' => 'text', 'regex' => "(0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])", 'dateformat' => 'm/d', 'placeholder' => 'mm/dd'],
		'std_disc_percent' => ['type' => 'number', 'max' => 100],
		'std_disc_days'    => ['type' => 'number', 'max' => 99],
		'std_disc_day'     => ['type' => 'number', 'max' => 31],
		'std_due_days'     => ['type' => 'number', 'max' => 990],
		'std_due_day'      => ['type' => 'number', 'max' => 31],
		'std_due_date'     => ['type' => 'text', 'regex' => "(0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])", 'dateformat' => 'm/d', 'placeholder' => 'mm/dd'],
		'std_plus_months'  => ['type' => 'number', 'max' => 99],
		'std_plus_years'   => ['type' => 'number', 'max' => 9],
		
	];
	const NBR_SPLITS_METHOD_S = 6;
	const NBR_SPLITS_METHOD_E = 3;
	const METHOD_STD = 'S';
	const METHOD_EOM = 'E';
	const TYPE_CREDITCARD = 'CC';

	protected static $instance;

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Return EOM (End-of-Month) Field Attributes
	 * @return string
	 */
	private function getEomFieldAttributes() {
		$attr = [];
		$attr['eom_thru_day'] = [
			'max' => $this->fieldAttribute('eom_thru_day', 'max'),
			'defaultToMaxAt' => $this->fieldAttribute('eom_thru_day', 'defaultToMaxAt'),
		];
		$attr['eom_due_day'] = [
			'max' => $this->fieldAttribute('eom_due_day', 'max'),
			'min' => $this->fieldAttribute('eom_due_day', 'min'),
		];
		return $attr;
	}

	/**
	 * Return EOM (End-of-Month) Input Opitons,
	 * NOTE: options format is used for ProcessWire\Sanitizer's float, int functions
	 * @return string
	 */
	private function getEomFieldInputOptions() {
		$opts = [];

		$opts['eom_due_day'] = [
			'max' => $this->fieldAttribute('eom_due_day', 'max'),
			'min' => $this->fieldAttribute('eom_due_day', 'min'),
		];
		$opts['eom_disc_percent'] = [
			'max'        => $this->fieldAttribute('eom_disc_percent', 'max'),
			'precision'  => $this->fieldAttribute('eom_disc_percent', 'precision'),
			'blankValue' => ''
		];
		$opts['eom_disc_day'] = [
			'max'        => $this->fieldAttribute('eom_disc_percent', 'max'),
			'blankValue' => ''
		];
		$opts['eom_disc_months'] = [
			'max'        => $this->fieldAttribute('eom_disc_months', 'max'),
			'blankValue' => ''
		];
		return $opts;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	public function code($id) {
		/** @var ArTermsCode */
		$code = parent::code($id);

		if ($code->method == self::METHOD_EOM) {
			for ($i = 2; $i <= self::NBR_SPLITS_METHOD_E; $i++) {
				$lastI = $i - 1;
	
				if (empty($code->eom_from_day($i)) && empty($code->eom_thru_day($lastI)) === false) {
					$nextFromDay = $code->eom_thru_day($lastI) + 1;
					if ($nextFromDay < $this->fieldAttribute('eom_thru_day', 'max')) {
						$code->set_eom_from_day($i, $nextFromDay);
						$code->set_eom_thru_day($i, $this->fieldAttribute('eom_thru_day', 'max'));
					}
					
				}
			}
		}
		
		return $code;
	}

	/**
	 * Return the IDs for the Purchase Order Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(ArTermsCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return Code as JSON Array
	 * @param  ArTermsCode $code
	 * @return array
	 */
	public function codeJson(Code $code) {
		$json = [];
		foreach (array_keys(static::FIELD_ATTRIBUTES) as $field) {
			if (isset($code->$field)) {
				$json[$field] = $code->$field;
			}
		}
		for ($i = 1; $i <= self::NBR_SPLITS_METHOD_E; $i++) {
			$json["eom_from_day$i"]     = $code->eom_from_day($i);
			$json["eom_thru_day$i"]     = $code->eom_thru_day($i);
			$json["eom_disc_percent$i"] = $code->eom_disc_percent($i);
			$json["eom_disc_day$i"]     = $code->eom_disc_day($i);
			$json["eom_disc_months$i"]  = $code->eom_disc_months($i);
			$json["eom_due_day$i"]      = $code->eom_due_day($i);
			$json["eom_plus_months$i"]  = $code->eom_plus_months($i);
		}
		return $json;
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @param  string $id
	 * @return ArTermsCode
	 */
	public function new($id = '') {
		/** @var ArTermsCode */
		$code = parent::new($id);
		$code->setMethod($this->fieldAttribute('method', 'default'));

		$code->setType($this->fieldAttribute('type', 'default'));
		$code->setArtmhold($this->fieldAttribute('hold', 'default'));
		$code->setExpiredate($this->fieldAttribute('expiredate', 'default'));
		$code->setCountry($this->fieldAttribute('country', 'default'));
		$code->setCcprefix($this->fieldAttribute('ccprefix', 'default'));
		$code->setFreightallow($this->fieldAttribute('freightallow', 'default'));
		$code->set_order_percent(1, 100.00);

		$code->set_eom_from_day(1, 1);
		$code->set_eom_thru_day(1, 99);

		// DEBUG:
		// $code->setMethod(self::METHOD_EOM);
		// $code->setCode('paul');

		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput       $input Input Data
	 * @param  ArTermsCode  $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields  = parent::_inputUpdate($input, $code);
		$invalidfieldsBasic = $this->_inputUpdateBasic($values, $code);
		$invalidfieldsMethods = $this->_inputUpdateMethodTerms($values, $code);
		// header('Content-Type: application/json; charset=utf-8');
		// echo json_encode($this->codeJson($code));
		// exit;	
		return array_merge($invalidfields, $invalidfieldsBasic);
	}

	/**
	 * Update Terms Code Basic fields
	 * @param  WireInputData $values
	 * @param  ArTermsCode   $code
	 * @return array
	 */
	private function _inputUpdateBasic(WireInputData $values, ArTermsCode $code) {
		$this->_inputUpdateMethod($values, $code);
		$this->_inputUpdateFreightallow($values, $code);
		$this->_inputUpdateType($values, $code);
		$this->_inputUpdateCcprefix($values, $code);
		$this->_inputUpdateCountry($values, $code);
		$this->_inputUpdateExpiredate($values, $code);

		$code->setArtmhold($values->yn('hold'));
		
		return [];
	}

	/**
	 * Update Terms Code Method
	 * @param  WireInputData $values
	 * @param  ArTermsCode   $code
	 * @return bool
	 */
	private function _inputUpdateMethod(WireInputData $values, ArTermsCode $code) {
		$method = $this->fieldAttribute('method', 'default');
		$methodOptions = $this->fieldAttribute('method', 'options');
		if ($values->option('method', array_keys($methodOptions))) {
			$method = $values->text('method');
		}
		$code->setMethod($method);
		return true;
	}

	/**
	 * Update Terms Code Method
	 * @param  WireInputData $values
	 * @param  ArTermsCode   $code
	 * @return bool
	 */
	private function _inputUpdateFreightallow(WireInputData $values, ArTermsCode $code) {
		$code->setFreightallow($values->yn('freightallow'));
		
		if ($code->method === self::METHOD_EOM) {
			$code->setFreightallow('N');
		}
		return true;
	}

	/**
	 * Update Terms Code Type
	 * NOTE: Code's Method should be updated first
	 * @param  WireInputData $values
	 * @param  ArTermsCode   $code
	 * @return bool
	 */
	private function _inputUpdateType(WireInputData $values, ArTermsCode $code) {
		$type = $this->fieldAttribute('type', 'default');
		$typeOptions = $this->fieldAttribute('type', 'optionsS');
		$inputName = 'typeS';

		if ($code->method == self::METHOD_EOM) {
			$inputName = 'typeE';
			$typeOptions = $this->fieldAttribute('type', 'optionsE');
		}

		if ($values->option($inputName, array_keys($typeOptions))) {
			$type = $values->text($inputName);
		}
		$code->setType($type);
		return true;
	}

	/**
	 * Update Terms Code's ccprefix
	 * @param  WireInputData $values
	 * @param  ArTermsCode   $code
	 * @return bool
	 */
	private function _inputUpdateCcprefix(WireInputData $values, ArTermsCode $code) {
		$code->setCcprefix('');

		if ($code->type != self::TYPE_CREDITCARD) {
			return true;
		}
		$CRCD = Crcd::instance();

		if ($CRCD->exists($values->string('ccprefix'))) {
			$code->setCcprefix($values->string('ccprefix'));
		}
		return true;
	}

	/**
	 * Update Terms Code's country
	 * @param  WireInputData $values
	 * @param  ArTermsCode   $code
	 * @return bool
	 */
	private function _inputUpdateCountry(WireInputData $values, ArTermsCode $code) {
		$code->setCountry('');

		$COCOM = Cocom::instance();

		if ($COCOM->exists($values->string('country'))) {
			$code->setCountry($values->string('country'));
		}
		return true;
	}

	/**
	 * Update Terms Code's expiredate
	 * @param  WireInputData $values
	 * @param  ArTermsCode   $code
	 * @return bool
	 */
	private function _inputUpdateExpiredate(WireInputData $values, ArTermsCode $code) {
		$code->setExpiredate('');
		$inputFormat = $this->fieldAttribute('expiredate', 'inputFormat');

		$opts = ['default' => '', 'returnFormat' => $this->fieldAttribute('expiredate', 'recordFormat')];
		$expireDate = $values->date('expiredate', $inputFormat, $opts);
		$minDate = date('m/d/Y');

		if (strtotime($expireDate) <= strtotime($minDate)) {
			$expireDate = '';
		}

		$code->setExpiredate($expireDate);
		return true;
	}

	/**
	 * Update Terms Code's Terms Splits dependign on Method Type
	 * @param  WireInputData $values
	 * @param  ArTermsCode   $code
	 * @return array
	 */
	private function _inputUpdateMethodTerms(WireInputData $values, ArTermsCode $code) {
		if ($code->method === self::METHOD_EOM) {
			return $this->_inputUpdateTermsEom($values, $code);
		}

		// Empty out EOM splits fields
		for ($i = 1; $i < self::NBR_SPLITS_METHOD_E; $i++) {
			$code->empty_eom_split($i);
		}
	}

	/**
	 * Update EOM Splits fields
	 * @param  WireInputData $values
	 * @param  ArTermsCode   $code
	 * @return void
	 */
	private function _inputUpdateTermsEom(WireInputData $values, ArTermsCode $code) {
		if ($code->method !== self::METHOD_EOM) {
			return [];
		}

		$code->set_eom_from_day(1, $this->fieldAttribute('eom_from_day1', 'default'));
		$code->set_eom_thru_day(1, $this->fieldAttribute('eom_thru_day1', 'default'));
		$nextFromDay = $code->eom_thru_day(1) + 1;

		$fieldAttr = $this->getEomFieldAttributes();
		$fieldOpts = $this->getEomFieldInputOptions();

		for ($i = 1; $i <= self::NBR_SPLITS_METHOD_E; $i++) {
			if ($nextFromDay > $fieldAttr['eom_thru_day']['max'] && $i > 1) {
				$code->empty_eom_split($i);
				continue;
			}
			if ($i > 1) {
				$code->set_eom_from_day($i, $nextFromDay);
			}
			$thruDay = $values->int("eom_thru_day$i", ['max' => $fieldAttr['eom_thru_day']['max']]);

			if ($thruDay > $fieldAttr['eom_thru_day']['defaultToMaxAt']) {
				$thruDay = $fieldAttr['eom_thru_day']['max'];
			}
			if ($thruDay <= $code->eom_from_day($i) && $i > 1) {
				$code->empty_eom_split($i);
				continue;
			}
			$code->set_eom_thru_day($i, $thruDay);
			$code->set_eom_due_day($i, $values->int("eom_due_day$i", $fieldOpts['eom_due_day']));
			$code->set_eom_disc_percent($i, $values->float("eom_disc_percent$i", $fieldOpts['eom_disc_percent']));

			if ($code->eom_disc_percent($i) > 0) {
				$code->set_eom_disc_day($i, $values->int("eom_disc_day$i", $fieldOpts['eom_disc_day']));
				$code->set_eom_disc_months($i, $values->int("eom_disc_months$i", $fieldOpts['eom_disc_months']));
			}
			$nextFromDay = $code->eom_thru_day($i) + 1;
		}
		return [];
	}
}
