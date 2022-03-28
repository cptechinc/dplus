<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use SalesPersonQuery, SalesPerson;
use VendorQuery, Vendor;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;
// Dplus Logm
use Dplus\Msa\Logm;

/**
 * Class that handles the CRUD of the SPM code table
 */
class Spm extends Base {
	const MODEL              = 'SalesPerson';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_saleper1';
	const DESCRIPTION        = 'Salesperson';
	const DESCRIPTION_RECORD = 'Salesperson';
	const RESPONSE_TEMPLATE  = 'Salesperson {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'spm';
	const DPLUS_TABLE           = 'SPM';
	const FIELD_ATTRIBUTES = [
		'id'      => ['type' => 'text', 'maxlength' => 6],
		'name'    => ['type' => 'text', 'maxlength' => 30],
		'userid'  => ['type' => 'text', 'maxlength' => 6],
		'groupid' => ['type' => 'text', 'maxlength' => 6],
		'cycle'   => ['type' => 'text', 'maxlength' => 2],
		'lastsaledate' => ['type' => 'text', 'format' => 'Ymd'],
	];

	/** @var self */
	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return SalesPerson
	 * @param  string $id SalesPerson ID
	 * @return SalesPerson
	 */
	public function rep($id) {
		return $this->code($id);
	}

	/**
	 * Return SalesPerson Name
	 * @param  string $id SalesPerson ID
	 * @return string
	 */
	public function name($id) {
		if ($this->exists($id) === false) {
			return '';
		}
		$model = static::modelClassName();
		$q = $this->queryId($id);
		$q->select($model::aliasproperty('name'));
		return $q->findOne();
	}

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'id'   => $code->id,
			'name' => $code->name
		];
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return SalesPerson
	 */
	public function new($id = '') {
		$code = new SalesPerson();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
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
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->text('id', ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);
		$invalidfields = [];

		$code          = $this->getOrCreate($id);
		$invalidfields = $this->_inputUpdate($input, $code);
		$response      = $this->saveAndRespond($code, $invalidfields);
		$this->setResponse($response);
		return $response->hasSuccess();
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
		$invalidfields = parent::_inputUpdate($input, $code);
		$invalidfieldsSpm = $this->_inputUpdateSpm($input, $code);
		$invalidfields = array_merge($invalidfields, $invalidfieldsSpm);
		return $invalidfields;
	}

	/**
	 * Update SalesPerson SPM fields
	 * @param  WireInput   $input     Input Data
	 * @param  SalesPerson $rep
	 * @return array
	 */
	private function _inputUpdateSpm(WireInput $input, SalesPerson $rep) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$rep->setName($values->text('name', ['maxLength' => $this->fieldAttribute('name', 'maxlength')]));
		$rep->setCycle($values->text('cycle', ['maxLength' => $this->fieldAttribute('cycle', 'maxlength')]));
		$rep->setManager($values->yn('manager'));
		$rep->setRestricted($values->yn('restricted'));
		$rep->setEmail($values->email('email'));

		$lastSaleDate = $values->text('lastsaledate') ? $values->date('lastsaledate', ['returnFormat' => $this->fieldAttribute('lastsaledate', 'format')]) : '';
		$rep->setLastsaledate($lastSaleDate);
		return $this->_inputUpdateSpmValidate($input, $rep);
	}

	/**
	 * Update SalesPerson SPM fields that are validated
	 * @param  WireInput   $input     Input Data
	 * @param  SalesPerson $rep
	 * @return array
	 */
	private function _inputUpdateSpmValidate(WireInput $input, SalesPerson $rep) {
		$rm = strtolower($input->requestMethod());
		$values  = $input->$rm;
		$invalid = [];

		// Validate Salesperson Group Code
		if (Spgpm::getInstance()->exists($values->text('groupid')) === false) {
			$invalid['groupid'] = 'Group';
		} else {
			$rep->setGroupid($values->text('groupid'));
		}

		// Validate Logm Login ID
		if (Logm::getInstance()->exists($values->text('userid')) === false) {
			$invalid['userid'] = 'Login ID';
		} else {
			$rep->setUserid($values->text('userid'));
		}

		// Validate Vendor ID
		if (boolval(VendorQuery::create()->filterById($values->text('vendorID'))->count()) === false) {
			$invalid['vendorID'] = 'Vendor ID';
		} else {
			$rep->setVendorID($values->text('vendorID'));
		}
		return $invalid;
	}

/* =============================================================
	Supplemntal
============================================================= */
	/**
	 * Return Group Code Description
	 * @param  string $id  Group ID / Code
	 * @return string
	 */
	public function groupCodeDescription($id) {
		$spgpm = Spgpm::getInstance();
		return $spgpm->exists($id) ? $spgpm->description($id) : '';
	}

	/**
	 * Return User's Name
	 * @param  string $id  User ID
	 * @return string
	 */
	public function logmUserName($id) {
		$logm = Logm::getInstance();
		return $logm->exists($id) ? $logm->name($id) : '';
	}

	/**
	 * Return Vendor's Name
	 * @param  string $id  Vendor ID
	 * @return string
	 */
	public function vendorName($id) {
		$q = VendorQuery::create()->filterById($id);
		$q->select(Vendor::aliasproperty('name'));
		return $q->count() ? $q->findOne() : '';
	}
}
