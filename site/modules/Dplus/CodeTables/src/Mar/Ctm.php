<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ArCustTypeCodeQuery, ArCustTypeCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTableEditableSingleKey;


/**
 * Class that handles the CRUD of the CTM code table
 *
 * @property Ctm\Qnotes $qnotes
 * @property bool       $updateGlAccts  Should GL Accounts be updated
 */
class Ctm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'ArCustTypeCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_type';
	const DESCRIPTION        = 'Customer Type Code';
	const DESCRIPTION_RECORD = 'Customer Type Code';
	const RESPONSE_TEMPLATE  = 'Customer Type Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'ctm';
	const DPLUS_TABLE           = 'CTM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => ArCustTypeCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 2],
		'maillist'    => ['type' => 'text', 'default' => 'N'],
		'email'       => ['type' => 'text', 'maxlength' => 50],
		'glsales'     => ['type' => 'text', 'title' => 'Sales'],
		'glcredits'   => ['type' => 'text', 'title' => 'Credits'],
		'glcogs'      => ['type' => 'text', 'title' => 'Cost of Goods Sold'],
		'glfreight'   => ['type' => 'text', 'title' => 'Freight (To Customer)'],
		'glmisc'      => ['type' => 'text', 'title' => 'Miscellaneous Amounts'],
		'glar'        => ['type' => 'text', 'title' => 'Accounts Receivable'],
		'glcash'      => ['type' => 'text', 'title' => 'Accounts Receivable Cash'],
		'glfinance'   => ['type' => 'text', 'title' => 'Finance Charge'],
		'gldiscounts' => ['type' => 'text', 'title' => 'Discounts Taken'],
	];
	/** @var self */
	protected static $instance;
	private $updateGlAccts = null;

	public function __construct() {
		parent::__construct();
		$this->qnotes = Ctm\Qnotes::instance();
	}

	/**
	 * Return if GL Accounts should be updated
	 * @return bool
	 */
	public function updateGlAccts() {
		if (is_bool($this->updateGlAccts) === true) {
			return $this->updateGlAccts;
		}
		$configAR = Configs\Ar::config();
		$this->updateGlAccts = $configAR->glReportType() == 'customer';
		return $this->updateGlAccts;
	}

	/**
	 * Return GL Account field Names
	 * @return array
	 */
	public function glaccounts() {
		return [
			'glsales', 'glcredits', 'glcogs', 'glfreight', 'glmisc',
			'glcash', 'glar', 'glfinance','gldiscounts',
		];
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		$sanitizer = $this->wire('sanitizer');

		return [
			'id'          => $code->id,
			'description' => $code->description,
			'maillist'    => $sanitizer->ynbool($code->maillist),
			'maillistYN'  => $code->maillist,
			'email'       => $code->email,
			'glaccounts'  => [
				'sales'     => $code->glsales,
				'credits'   => $code->glcredits,
				'cogs'      => $code->glcogs,
				'freight'   => $code->glfreight,
				'misc'      => $code->glmisc,
				'cash'      => $code->glcash,
				'ar'        => $code->glar,
				'finance'   => $code->glfinance,
				'discounts' => $code->gldiscounts,
			]
		];
	}

/* =============================================================
	CRUD Creates
============================================================= */

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Code      $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields   = parent::_inputUpdate($input, $code);
		$invalidfieldsCtm = $this->_inputUpdateCtm($input, $code);
		$invalidfields = array_merge($invalidfields, $invalidfieldsCtm);
		return $invalidfields;
	}

	/**
	 * Update ArCustTypeCode CTM fields
	 * @param  WireInput      $input     Input Data
	 * @param  ArCustTypeCode $code
	 * @return array
	 */
	private function _inputUpdateCtm(WireInput $input, ArCustTypeCode $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$code->setMaillist($values->yn('maillist'));
		$code->setEmail($values->email('email'));
		$invalidfields = $this->_inputUpdateGlAccts($input, $code);
		return $invalidfields;
	}

	/**
	 * Update ArCustTypeCode GL Accounts
	 * @param  WireInput      $input     Input Data
	 * @param  ArCustTypeCode $code
	 * @return array
	 */
	private function _inputUpdateGlAccts(WireInput $input, ArCustTypeCode $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = [];

		if ($this->updateGlAccts() === false) {
			return $invalidfields;
		}

		$mhm = Codes\Mgl\Mhm::getInstance();

		foreach ($this->glaccounts() as $field) {
			if ($mhm->exists($values->text($field)) === false) {
				$invalidfields[$fields] = $this->fieldAttribute($field, 'title');
			} else {
				$setAcct = 'set' . ucfirst($field);
				$code->$setAcct($values->text($field));
			}
		}
		return $invalidfields;
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return GL Account Description
	 * @param  string $id GL Code
	 * @return string
	 */
	public function glAcctDescription($id) {
		$mhm = Codes\Mgl\Mhm::getInstance();
		return $mhm->exists($id) ? $mhm->description($id) : '';
	}
}
