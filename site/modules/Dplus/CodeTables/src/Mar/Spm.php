<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Models
use SalesPerson;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use ProcessWire\WireInputData;

/**
 * Class that handles the CRUD of the SPM table
 */
class Spm extends AbstractCodeTableEditableSingleKey {
	const MODEL 			 = 'SalesPerson';
	const MODEL_KEY 		 = 'code';
	const MODEL_TABLE		 = 'ar_saleper1';
	const DESCRIPTION		 = 'Salesperson';
	const DESCRIPTION_RECORD = 'Salesperson';
	const RESPONSE_TEMPLATE  = 'Salesperson {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'spm';
	const DPLUS_TABLE			= 'SPM';
	const FIELD_ATTRIBUTES = [
		'id'     => ['type' => 'text', 'maxlength' => 6],
		'code'     => ['type' => 'text', 'maxlength' => 6],
		'name'   => ['type' => 'text', 'maxlength' => 30],
		'cycle'  => ['type' => 'text', 'maxlength' => 2],
		'groupid' => ['type' => 'text', 'maxlength' => Spgpm::FIELD_ATTRIBUTES['code']['maxlength']],
		'salesmtd' => ['type' => 'number', 'precision' => 2, 'max' => 99999999.99, 'label' => 'Sales MTD'],
		'salesytd' => ['type' => 'number', 'precision' => 2, 'max' => 99999999.99, 'label' => 'Sales YTD'],
		'salesltd' => ['type' => 'number', 'precision' => 2, 'max' => 99999999.99, 'label' => 'Sales LTD'],
		'earnedmtd' => ['type' => 'number', 'precision' => 2, 'max' => 99999999.99, 'label' => 'Earned MTD'],
		'earnedytd' => ['type' => 'number', 'precision' => 2, 'max' => 99999999.99, 'label' => 'Earned YTD'],
		'earnedltd' => ['type' => 'number', 'precision' => 2, 'max' => 99999999.99, 'label' => 'Earned LTD'],
		'paidmtd' => ['type' => 'number', 'precision' => 2, 'max' => 99999999.99, 'label' => 'Paid MTD'],
		'paidytd' => ['type' => 'number', 'precision' => 2, 'max' => 99999999.99, 'label' => 'Paid YTD'],
		'paidltd' => ['type' => 'number', 'precision' => 2, 'max' => 99999999.99, 'label' => 'Paid LTD'],
		'manager' => ['type' => 'text', 'options' => ['Y' => 'Yes', 'N' => 'No'], 'default' => 'N'],
		'restricted' => ['type' => 'text', 'options' => ['Y' => 'Yes', 'N' => 'No'], 'default' => 'Y'],
		'lastsaledate' => ['type' => 'text', 'format' => 'Ymd', 'displayformat' => 'm/d/Y'],
		'userid'      => ['type' => 'text', 'maxlength' => 6],
		'email'      => ['type' => 'text'],
		'vendorid'      => ['type' => 'text'],
	];

	public function codeJson(Code $code) {
		$json = parent::codeJson($code);
		foreach (array_keys(static::FIELD_ATTRIBUTES) as $field) {
			$json[$field] = $code->$field;
		}
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
		$q->select(Salesperson::aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return Sales Person
	 * @param  string $id Sales Person ID
	 * @return SalesPerson
	 */
	public function salesperson($id) {
		$q = $this->query();
		$q->filterById($id);
		return $q->findOne();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return new Salesperson
	 * @param  string $id Code
	 * @return Salesperson
	 */
	public function new($id = '') {
		$code = new Salesperson();

		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		$code->setManager($this->fieldAttribute('manager', 'default'));
		$code->setRestricted($this->fieldAttribute('restricted', 'default'));
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput	     $input Input Data
	 * @param  Salesperson  $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields  = parent::_inputUpdate($input, $code);
		$invalidfieldsVA = $this->_inputUpdateValidatedFields($values, $code);
		$invalidfieldsSA = $this->_inputUpdateSales($values, $code);
		$invalidfieldsPE = $this->_inputUpdatePermissions($values, $code);
		$invalidfieldsEM = $this->_inputUpdateEmail($values, $code);
		$invalidfieldsCY = $this->_inputUpdateCycle($values, $code);

		$invalidfields = array_merge($invalidfields, $invalidfieldsVA, $invalidfieldsSA, $invalidfieldsPE, $invalidfieldsEM, $invalidfieldsCY);
		return $invalidfields;
	}

	/**
	 * Update Sales fields
	 * @param  WireInputData $values
	 * @param  Salesperson $code
	 * @return array
	 */
	private function _inputUpdateSales(WireInputData $values, Salesperson $code) {
		$fields = ['salesmtd', 'salesytd','salesltd', 'earnedmtd', 'earnedytd','earnedltd', 'paidmtd', 'paidytd', 'paidltd'];

		foreach ($fields as $field) {
			$fieldOptions = [
				'precision' => $this->fieldAttribute($field, 'precision'),
				'max'       => $this->fieldAttribute($field, 'max')
			];
			$setField = 'set'.ucfirst($field);
			$code->$setField($values->float($field, $fieldOptions));
		}

		if ($values->text('lastsaledate') != '') {
			$code->setLastsaledate(date($this->fieldAttribute('lastsaledate', 'format'), strtotime($values->text('lastsalesdate'))));
		}
		return [];
	}

	/**
	 * Update fields that need validation, return errors
	 * NOTE: fields are groupid, userid, vendorid
	 * @param  WireInputData    $values
	 * @param  Salesperson      $code
	 * @return array
	 */
	private function _inputUpdateValidatedFields(WireInputData $values, Salesperson $code) {
		$invalidfields = [];
		$originals = ['groupid' => $code->groupid, 'userid' => $code->userid, 'vendorid' => $code->vendorid];

		$spgpm = Spgpm::instance();
		$code->setGroupid($values->text('groupid'));

		if ($spgpm->exists($values->text('groupid')) === false) {
			$code->setGroupid($originals['groupid']);
			$invalidfields['groupid'] = 'Group ID';
		}

		$logm = \Dplus\Msa\Logm::getInstance();
		$code->setUserid($values->text('userid'));

		if ($logm->exists($values->text('userid')) === false) {
			$code->setUserid($originals['userid']);
			$invalidfields['userid'] = 'Login ID';
		}

		$vendors = \VendorQuery::create();
		$code->setVendorid($values->text('vendorid'));

		if (boolval($vendors->filterByVendorid($values->text('vendorid'))->count()) === false) {
			$code->setVendorid($originals['vendorid']);
			$invalidfields['vendorid'] = 'Vendor ID';
		}
		return $invalidfields;
	}

	/**
	 * Set SPM Permissions (Manager, restrictions)
	 * @param WireInputData $values
	 * @param Salesperson   $code
	 * @return array
	 */
	private function _inputUpdatePermissions(WireInputData $values, Salesperson $code) {
		$code->setManager($values->yn('manager'));
		$code->setRestricted($values->yn('restricted'));
		return [];
	}

	/**
	 * Set Email
	 * @param WireInputData $values
	 * @param Salesperson   $code
	 * @return array
	 */
	private function _inputUpdateEmail(WireInputData $values, Salesperson $code) {
		$code->setEmail($values->email('email'));
		return [];
	}

	/**
	 * Set Email
	 * @param WireInputData $values
	 * @param Salesperson   $code
	 * @return array
	 */
	private function _inputUpdateCycle(WireInputData $values, Salesperson $code) {
		$code->setCycle($values->text('cycle', ['maxLength' => $this->fieldAttribute('cycle', 'maxlength')]));
		return [];
	}
}
