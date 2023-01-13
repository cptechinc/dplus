<?php namespace Dplus\Codes\Mar;
// Dplus Models
use ArTermsCode;
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
		'method'       => ['type' => 'text', 'default' => 'S', 'options' => ['S' => 'Standard Terms', 'E' => 'End of Menth Terms']],
		'type'         => ['type' => 'text', 'default' => 'STD', 'optionsS' => ['STD' => 'Standard', 'CC' => 'Credit Card', 'CA' => 'Cash', 'COD' => 'COD', 'CDC' => 'COD Cash'], 'optionsE' => ['STD' => 'Standard', 'CA' => 'Cash', 'COD' => 'COD', 'CDC' => 'COD Cash' ]],
		'hold'         => ['type' => 'text', 'default' => 'N', 'options' => ['Y' => 'Yes', 'N' => 'No']],
		'expiredate'   => ['type' => 'text', 'default' => ''],
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
		'eom_thru_day'     => ['type' => 'number', 'max' => 99],

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
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Purchase Order Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(ArTermsCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @param  string $id
	 * @return Code
	 */
	public function new($id = '') {
		$code = parent::new($id);
		$code->method       = $this->fieldAttribute('method', 'default');
		$code->type         = $this->fieldAttribute('type', 'default');
		$code->hold         = $this->fieldAttribute('hold', 'default');
		$code->expiredate   = $this->fieldAttribute('expiredate', 'default');
		$code->country      = $this->fieldAttribute('country', 'default');
		$code->ccprefix     = $this->fieldAttribute('ccprefix', 'default');
		$code->freightallow = $this->fieldAttribute('freightallow', 'default');
		$code->set_order_percent(1, 100.00);

		$code->set_eom_from_day(1, 1);
		$code->set_eom_thru_day(1, 99);

		// DEBUG:
		// $code->method = self::METHOD_EOM;
		// $code->code = 'test';

		return $code;
	}
}
