<?php namespace Dplus\Codes\Map;
// Propel ORM Library
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// Dplus Models
use ApTermsCode;
// ProcessWire
use ProcessWire\WireInput;
use ProcessWire\WireInputData;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Configs;

/**
 * Class that handles the CRUD of the PTM code table
 */
class Ptm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'ApTermsCode';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'ap_term_code';
	const DESCRIPTION        = 'Vendor Terms Code';
	const DESCRIPTION_RECORD = 'Vendor Terms Code';
	const RESPONSE_TEMPLATE  = 'Vendor Terms Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'ptm';
	const DPLUS_TABLE           = 'PTM';
	const FIELD_ATTRIBUTES = [
		'code'         => ['type' => 'text', 'maxlength' => 4],
		'description'  => ['type' => 'text', 'maxlength' => 20],
		'method'       => ['type' => 'text', 'default' => 'S', 'optionsShort' => ['S' => 'STD', 'E' => 'EOM'], 'options' => ['S' => 'Standard Terms', 'E' => 'End of Month Terms']],
		'expiredate'   => ['type' => 'text', 'default' => '', 'inputFormat' => 'm/d/Y', 'recordFormat' => 'Ymd'],

		// THESE ARE FOR THE SPLITS, USE BASE NAME
		'std_order_percent'    => ['type' => 'number', 'max' => 100.00, 'precision' => 2],
		'std_disc_date'    => ['type' => 'text', 'default' => '0101', 'regex' => "(0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])", 'dateformat' => 'm/d', 'recordFormat' => 'md', 'placeholder' => 'mm/dd'],
		'std_disc_percent' => ['type' => 'number', 'max' => 100.00, 'precision' => 2],
		'std_disc_days'    => ['type' => 'number', 'max' => 999],
		'std_disc_day'     => ['type' => 'number', 'max' => 31],
		'std_due_days'     => ['type' => 'number', 'default' => 30, 'max' => 999],
		'std_due_day'      => ['type' => 'number', 'max' => 31],
		'std_due_date'     => ['type' => 'text', 'default' => '0101', 'regex' => "(0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])", 'dateformat' => 'm/d', 'recordFormat' => 'md', 'placeholder' => 'mm/dd'],
		'std_plus_months'  => ['type' => 'number', 'max' => 99],
		'std_plus_years'   => ['type' => 'number', 'max' => 9],
		
		// THESE ARE FOR THE SPLITS, USE BASE NAME
		'eom_from_day1'     => ['type' => 'number',  'default' => 1],
		'eom_thru_day1'     => ['type' => 'number', 'max' => 99, 'default' => 99],
		'eom_disc_percent' => ['type' => 'number', 'max' => 100, 'precision' => 2],
		'eom_disc_day'     => ['type' => 'number', 'max' => 31],
		'eom_disc_months'  => ['type' => 'number', 'max' => 99],
		'eom_due_day'      => ['type' => 'number', 'max' => 31, 'min' => 1],
		'eom_plus_months'  => ['type' => 'number', 'max' => 99],
		'eom_from_day'     => ['type' => 'number', 'max' => 98],
		'eom_thru_day'     => ['type' => 'number', 'max' => 99, 'defaultToMaxAt' => 30],
	];
	const NBR_SPLITS_METHOD_STD = 5;
	const NBR_SPLITS_METHOD_EOM = 3;
	const METHOD_STD = 'S';
	const METHOD_EOM = 'E';

	protected static $instance;
	protected $fieldAttributes;

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Return EOM (End-of-Month) Field Attributes
	 * @return array
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
	 * @return array
	 */
	private function getEomFieldInputOptions() {
		$opts = [];

		$opts['eom_due_day'] = [
			'max' => $this->fieldAttribute('eom_due_day', 'max'),
			'min' => $this->fieldAttribute('eom_due_day', 'min'),
		];
		$opts['eom_plus_months'] = [
			'max' => $this->fieldAttribute('eom_plus_months', 'max'),
			'blankValue' => ''
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

	/**
	 * Return STD Discount Input Options
	 * NOTE: options format is used for ProcessWire\Sanitizer's float, int functions
	 * @return array
	 */
	private function getStdDiscountFieldInputOptions() {
		$opts = [];
		$opts['std_disc_percent'] = [
			'max'       => $this->fieldAttribute('std_disc_percent', 'max'),
			'precision' => $this->fieldAttribute('std_disc_percent', 'precision')
		];
		$opts['std_disc_date'] = [
			'dateformat'       => $this->fieldAttribute('std_disc_date', 'dateformat'),
			'returnFormat'     => $this->fieldAttribute('std_disc_date', 'recordFormat')
		];
		$opts['std_disc_days'] = [
			'max'       => $this->fieldAttribute('std_disc_days', 'max'),
		];
		$opts['std_disc_day'] = [
			'max'       => $this->fieldAttribute('std_disc_day', 'max'),
		];
		return $opts;
	}

	/**
	 * Return STD Due Input Options
	 * NOTE: options format is used for ProcessWire\Sanitizer's float, int functions
	 * @return array
	 */
	private function getStdDueFieldInputOptions() {
		$opts = [];

		$opts['std_due_days'] = [
			'max'      => $this->fieldAttribute('std_due_days', 'max'),
			'default'  => $this->fieldAttribute('std_due_days', 'default'),
		];
		$opts['std_due_day'] = [
			'max'  => $this->fieldAttribute('std_due_day', 'max'),
		];
		$opts['std_plus_months'] = [
			'max'  => $this->fieldAttribute('std_plus_months', 'max'),
		];
		$opts['std_due_date'] = [
			'dateformat'    => $this->fieldAttribute('std_due_date', 'dateformat'),
			'returnFormat'  => $this->fieldAttribute('std_due_date', 'recordFormat')
		];

		$opts['std_plus_years'] = [
			'max'  => $this->fieldAttribute('std_plus_years', 'max'),
		];
		return $opts;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	public function code($id) {
		/** @var ApTermsCode */
		$code = parent::code($id);

		$this->setDefaults($code);

		if ($code->method == self::METHOD_EOM) {
			for ($i = 2; $i <= self::NBR_SPLITS_METHOD_EOM; $i++) {
				$lastI = $i - 1;
	
				if (empty($code->eom_from_day($i)) && empty($code->eom_thru_day($lastI)) === false) {
					$nextFromDay = $code->eom_thru_day($lastI) + 1;
					if ($nextFromDay < $this->fieldAttribute('eom_thru_day', 'max')) {
						$code->setEom_from_day($i, $nextFromDay);
						$code->setEom_thru_day($i, $this->fieldAttribute('eom_thru_day', 'max'));
					}	
				}
			}
			
		}
		return $code;
	}

	public function setDefaults(ApTermsCode $code) {
		if (empty($code->eom_from_day(1))) {
			$code->setEom_from_day(1, 1);
			$code->setEom_thru_day(1, 99);
		}

		if ($code->std_order_percent(1) == 0) {
			$code->setStd_order_percent(1, 100);
		}
	}

	/**
	 * Return the IDs for the Purchase Order Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(ApTermsCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return Code as JSON Array
	 * @param  ApTermsCode $code
	 * @return array
	 */
	public function codeJson(Code $code) {
		$json = [];
		foreach (array_keys(static::FIELD_ATTRIBUTES) as $field) {
			if (isset($code->$field)) {
				$json[$field] = $code->$field;
			}
		}
		for ($i = 1; $i <= self::NBR_SPLITS_METHOD_EOM; $i++) {
			$json["eom_from_day$i"]     = $code->eom_from_day($i);
			$json["eom_thru_day$i"]     = $code->eom_thru_day($i);
			$json["eom_disc_percent$i"] = $code->eom_disc_percent($i);
			$json["eom_disc_day$i"]     = $code->eom_disc_day($i);
			$json["eom_disc_months$i"]  = $code->eom_disc_months($i);
			$json["eom_due_day$i"]      = $code->eom_due_day($i);
			$json["eom_plus_months$i"]  = $code->eom_plus_months($i);
		}
		for ($i = 1; $i <= self::NBR_SPLITS_METHOD_STD; $i++) {
			$json["std_order_percent$i"]  = $code->std_order_percent($i);
			$json["std_disc_percent$i"]   = $code->std_disc_percent($i);
			$json["std_disc_days$i"]      = $code->std_disc_days($i);
			$json["std_disc_day$i"]       = $code->std_disc_day($i);
			$json["std_disc_date$i"]      = $code->std_disc_date($i);
			$json["std_due_days$i"]       = $code->std_due_days($i);
			$json["std_due_day$i"]        = $code->std_due_day($i);
			$json["std_plus_months$i"]    = $code->std_plus_months($i);
			$json["std_due_date$i"]       = $code->std_due_date($i);
			$json["std_plus_years$i"]     = $code->std_plus_years($i);
		}
		return $json;
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @param  string $id
	 * @return ApTermsCode
	 */
	public function new($id = '') {
		/** @var ApTermsCode */
		$code = parent::new($id);
		$code->setMethod($this->fieldAttribute('method', 'default'));
		$code->setStd_order_percent(1, 100.00);
		$code->setEom_from_day(1, 1);
		$code->setEom_thru_day(1, 99);
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput       $input Input Data
	 * @param  ApTermsCode  $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields  = parent::_inputUpdate($input, $code);
		$invalidfieldsBasic = $this->_inputUpdateBasic($values, $code);
		$invalidfieldsMethods = $this->_inputUpdateMethodTerms($values, $code);
		return array_merge($invalidfields, $invalidfieldsBasic, $invalidfieldsMethods);
	}

	/**
	 * Update Terms Code Basic fields
	 * @param  WireInputData $values
	 * @param  ApTermsCode   $code
	 * @return array
	 */
	private function _inputUpdateBasic(WireInputData $values, ApTermsCode $code) {
		$this->_inputUpdateMethod($values, $code);
		$this->_inputUpdateExpiredate($values, $code);
		return [];
	}

	/**
	 * Update Terms Code Method
	 * @param  WireInputData $values
	 * @param  ApTermsCode   $code
	 * @return bool
	 */
	private function _inputUpdateMethod(WireInputData $values, ApTermsCode $code) {
		$method = $this->fieldAttribute('method', 'default');
		$methodOptions = $this->fieldAttribute('method', 'options');
		if ($values->option('method', array_keys($methodOptions))) {
			$method = $values->text('method');
		}
		$code->setMethod($method);
		return true;
	}

	/**
	 * Update Terms Code's expiredate
	 * @param  WireInputData $values
	 * @param  ApTermsCode   $code
	 * @return bool
	 */
	private function _inputUpdateExpiredate(WireInputData $values, ApTermsCode $code) {
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
	 * @param  ApTermsCode   $code
	 * @return array
	 */
	private function _inputUpdateMethodTerms(WireInputData $values, ApTermsCode $code) {
		if ($code->method === self::METHOD_EOM) {
			for ($i = 1; $i <= self::NBR_SPLITS_METHOD_STD; $i++) {
				$code->emptyStdSplit($i);
			}
			return $this->_inputUpdateTermsEom($values, $code);
		}

		// Empty out EOM splits fields
		for ($i = 1; $i <= self::NBR_SPLITS_METHOD_EOM; $i++) {
			$code->emptyEomSplit($i);
		}
		$this->_inputUpdateTermsStd($values, $code);
		return [];
	}

	/**
	 * Update EOM Splits fields
	 * @param  WireInputData $values
	 * @param  ApTermsCode   $code
	 * @return void
	 */
	private function _inputUpdateTermsEom(WireInputData $values, ApTermsCode $code) {
		if ($code->method !== self::METHOD_EOM) {
			return [];
		}

		$code->setEom_from_day(1, $this->fieldAttribute('eom_from_day1', 'default'));
		$code->setEom_thru_day(1, $this->fieldAttribute('eom_thru_day1', 'default'));
		$nextFromDay = $code->eom_thru_day(1) + 1;

		$fieldAttr = $this->getEomFieldAttributes();
		$fieldOpts = $this->getEomFieldInputOptions();

		for ($i = 1; $i <= self::NBR_SPLITS_METHOD_EOM; $i++) {
			if ($nextFromDay > $fieldAttr['eom_thru_day']['max'] && $i > 1) {
				$code->emptyEomSplit($i);
				continue;
			}
			if ($i > 1) {
				$code->setEom_from_day($i, $nextFromDay);
			}
			$thruDay = $values->int("eom_thru_day$i", ['max' => $fieldAttr['eom_thru_day']['max']]);

			if ($thruDay > $fieldAttr['eom_thru_day']['defaultToMaxAt']) {
				$thruDay = $fieldAttr['eom_thru_day']['max'];
			}
			if ($thruDay <= $code->eom_from_day($i) && $i > 1) {
				$code->emptyEomSplit($i);
				continue;
			}
			$code->setEom_thru_day($i, $thruDay);
			$code->setEom_due_day($i, $values->int("eom_due_day$i", $fieldOpts['eom_due_day']));
			$code->setEom_plus_months($i, $values->int("eom_plus_months$i", $fieldOpts['eom_plus_months']));
			$code->setEom_disc_percent($i, $values->float("eom_disc_percent$i", $fieldOpts['eom_disc_percent']));

			if ($code->eom_disc_percent($i) > 0) {
				$code->setEom_disc_day($i, $values->int("eom_disc_day$i", $fieldOpts['eom_disc_day']));
				$code->setEom_disc_months($i, $values->int("eom_disc_months$i", $fieldOpts['eom_disc_months']));
			}
			$nextFromDay = $code->eom_thru_day($i) + 1;
		}
		return [];
	}

	/**
	 * Update STD Splits fields
	 * @param  WireInputData $values
	 * @param  ApTermsCode   $code
	 * @return void
	 */
	private function _inputUpdateTermsStd(WireInputData $values, ApTermsCode $code) {
		if ($code->method != self::METHOD_STD) {
			return [];
		}
		$this->setStdFields($values, $code);
		$this->sumUpOrderPercentsTo100($values, $code);
		return [];
	}

	/**
	 * Ensure that Order Percent total adds up to 100
	 * @param  WireInputData $values
	 * @param  ApTermsCode   $code
	 * @return bool
	 */
	private function sumUpOrderPercentsTo100(WireInputData $values, ApTermsCode $code) {
		$totalOrderPercent = 0;
		$lastIndex = 1;

		for ($i = 1; $i <= self::NBR_SPLITS_METHOD_STD; $i++) {
			if ($totalOrderPercent >= 100) {
				continue;
			}
			$percent = $code->std_order_percent($i);
			if ($percent) {
				$lastIndex = $i;
			}
			$totalOrderPercent += $percent;
		}

		// IF TOTAL ORDER PERCENT DOES NOT ADD UP TO 100 BEEF UP THE LAST INDEX 
		if ($totalOrderPercent < 100) {
			$percent = $code->std_order_percent($lastIndex);
			$percentNeeded = 100 - $percent;
			$code->setStd_order_percent($lastIndex, $percentNeeded);
		}
		return true;
	}

	/**
	 * Set STD Method Fields
	 * @param  WireInputData $values
	 * @param  ApTermsCode   $code
	 * @return array
	 */
	private function setStdFields(WireInputData $values, ApTermsCode $code) {
		$totalOrderPercent = 0;

		$opts = [];
		$opts['std_order_percent'] = [
			'max'       => $this->fieldAttribute('std_order_percent', 'max'), 
			'precision' => $this->fieldAttribute('std_order_percent', 'precision')
		];

		for ($i = 1; $i <= self::NBR_SPLITS_METHOD_STD; $i++) {
			if ($totalOrderPercent >= $opts['std_order_percent']['max']) {
				$code->emptyStdSplit($i);
				continue;
			}
			// IF THIS SPLIT MAKES THE ORDER PERCENT OVER HUNDRED THEN MAKE THIS THE EXACT PERCENT
			// NEEDED TO GET TO A 100
			$subtotal     = $totalOrderPercent;
			$orderPercent = $values->float("std_order_percent$i", $opts['std_order_percent']);
			$percentNeededTo100 = 100 - $subtotal;

			if ($orderPercent > $percentNeededTo100) {
				$orderPercent = $percentNeededTo100;
			}
			$code->setStd_order_percent($i, $orderPercent);
			$totalOrderPercent += $code->std_order_percent($i);
			$this->setStdDiscountFieldsAtIndex($values, $code, $i);
			$this->setStdDueFieldsAtIndex($values, $code, $i);
		}
		return [];
	}

	/**
	 * Set Discount Fields at Split Index
	 * Fields: percent, days, day, date
	 * NOTE: if days, day, date are all blank 
	 * then the discount perceent will be removed for record integrity
	 * @param  WireInputData $values
	 * @param  ApTermsCode   $code
	 * @param  int           $i       Index
	 * @return bool
	 */
	private function setStdDiscountFieldsAtIndex(WireInputData $values, ApTermsCode $code, int $i) {
		if ($code->method !== self::METHOD_STD) {
			return false;
		}
		$opts = $this->getStdDiscountFieldInputOptions();
		$code->emptyStdDiscFields($i);

		$discPercent = $values->float("std_disc_percent$i", $opts['std_disc_percent']);
		$code->setStd_disc_percent($i, $discPercent);
		// EMPTY DISCOUNT FIELDS REGARDLESS
		$code->setStd_disc_days($i, '');
		$code->setStd_disc_day($i, '');
		$code->setStd_disc_date($i, '');

		if (empty($discPercent)) {
			$code->setStd_disc_percent($i, '');
			return true;
		}
		
		// DISCOUNT DAYS
		$days = $values->int("std_disc_days$i" , $opts['std_disc_days']);

		if ($days > 0) {
			$code->setStd_disc_days($i, $days);
			return true;
		}

		// DISCOUNT DAY OF MONTH
		$day = $values->int("std_disc_day$i" , $opts['std_disc_day']);

		if ($day > 0) {
			$code->setStd_disc_day($i, $day);
			return true;
		}

		// DISCOUNT DATE
		$date = $values->date("std_disc_date$i", $opts['std_disc_date']['dateformat'], $opts['std_disc_date']);

		// echo "std_disc_date$i : " . "format: ". $opts['std_disc_date']['dateformat'] . " - " . $date . '<br>';

		if (empty($date)) {// VALUES HAVE BEEN INVALID SO FAR, So remove discount percent
			$code->setStd_disc_percent($i, '');
			return false;
		}

		$code->setStd_disc_date($i, $date);
		return true;
	}

	/**
	 * Set Due Fields at Split Index
	 * FIELDS: days, day, plus months, date, plus years
	 * NOTE: due days, day, date are all blank 
	 * then the days is set to default for record integrity
	 * @param  WireInputData $values
	 * @param  ApTermsCode   $code 
	 * @param  int           $i      Split Index
	 * @return void
	 */
	private function setStdDueFieldsAtIndex(WireInputData $values, ApTermsCode $code, int $i) {
		if ($code->method != self::METHOD_STD) {
			return false;
		}
		$opts = $this->getStdDueFieldInputOptions();
		$code->emptyStdDueFields($i);

		// DUE DAYS
		$days = $values->int("std_due_days$i" , $opts['std_due_days']);

		if ($days > 0) {
			$code->setStd_due_days($i, $days);
			return true;
		}

		// DUE DAY
		$day = $values->int("std_due_day$i" , $opts['std_due_day']);

		if ($day > 0) {
			$code->setStd_due_day($i, $day);
			$months = $values->int("std_plus_months$i" , $opts['std_plus_months']);
			if ($months > 0) {
				$code->setStd_plus_months($i, $months);
			}
			return true;
		}

		// DUE DATE
		$date = $values->date("std_due_date$i", $opts['std_due_date']['dateformat'], $opts['std_due_date']);

		if (empty($date)) { // VALUES HAVE BEEN INVALID SO FAR, SET DAYS TO DEFAULT FOR RECORD INTEGRITY
			$code->setStd_due_days($i, $opts['std_due_days']['max']);
			return false;
		}
		$code->setStd_due_date($i, $date);

		$years = $values->int("std_plus_years$i" , $opts['std_plus_years']);
		if ($years) {
			$code->setStd_plus_years($i, $years);
		}
		return true;
	}
}
