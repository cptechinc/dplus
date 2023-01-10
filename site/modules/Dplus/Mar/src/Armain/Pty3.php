<?php namespace Dplus\Mar\Armain;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
// ProcessWire
use ProcessWire\WireInput;
use ProcessWire\WireInputData;
// Dplus Models
use ArCust3partyFreightQuery, ArCust3partyFreight;
// Dplus Databases
use Dplus\Databases\Connectors\Dplus as DbDplus;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Filters
use Dplus\Filters;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
// Dplus Crud
use Dplus\Crud\AbstractManager;
use Dplus\Crud\Response;

/**
 * Class that handles the CRUD of the ArCust3partyFreight Table
 */
class Pty3 extends AbstractManager {
	const MODEL              = 'ArCust3partyFreight';
	const MODEL_KEY          = 'custid,accountnbr';
	const MODEL_TABLE        = 'ar_3party';
	const DESCRIPTION        = 'Cust 3rd Party Freight';
	const DESCRIPTION_RECORD = 'Cust 3rd Party Freight';
	const RESPONSE_TEMPLATE  = 'Cust 3rd Party Freight {key} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'pty3';
	const DPLUS_TABLE           = 'PTY3';
	const FIELD_ATTRIBUTES = [
		'custid'         => ['type' => 'text'],
		'accountnbr'     => ['type' => 'text', 'maxlength' => ArCust3partyFreight::MAX_LENGTH_ACCOUNTNBR],
		'name'           => ['type' => 'text', 'maxlength' => 30],
		'address1'       => ['type' => 'text', 'maxlength' => 30],
		'address2'       => ['type' => 'text', 'maxlength' => 30],
		'address3'       => ['type' => 'text', 'maxlength' => 30],
		'city'           => ['type' => 'text', 'maxlength' => 16],
		'state'          => ['type' => 'text', 'maxlength' => 2],
		'zip'            => ['type' => 'text', 'maxlength' => 10],
		'country'        => ['type' => 'text', 'maxlength' => 4],
		'international'  => ['type' => 'text', 'default' => 'N', 'options' => ['Y' => 'Yes', 'N' => 'No']],
		'phone'          => ['type' => 'text', 'maxlength' => 10, 'formatmaskinput' => 'XXX-XXX-XXX'],
		'extension'      => ['type' => 'text', 'maxlength' => 7],
		'fax'            => ['type' => 'text', 'maxlength' => 10, 'formatmaskinput' => 'XXX-XXX-XXX'],
		'phoneintl'      => ['type' => 'text', 'maxlength' => 22, 'formatmaskinput' => 'XXX-XXXX-XXXXXXXXXXXXXXX'],
		'extensionintl'  => ['type' => 'text', 'maxlength' => 7],
		'faxintl'        => ['type' => 'text', 'maxlength' => 22, 'formatmaskinput' => 'XXX-XXXX-XXXXXXXXXXXXXXX'],
	];

	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return Query Filtered by Customer ID
	 * @param  string $custID Customer ID
	 * @return ArCust3partyFreightQuery
	 */
	public function queryCustId($custID) {
		return $this->query()->filterByCustid($custID);
	}

	/**
	 * Return Query Filtered by Customer ID, Account Number
	 * @param  string $custID  Customer ID
	 * @param  string $acctnbr Account Number
	 * @return ArCust3partyFreightQuery
	 */
	public function queryCustAccount($custID, $acctnbr) {
		return $this->query()->filterByCustid($custID)->filterByAccountnbr($acctnbr);
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return All Custids
	 * @return array
	 */
	public function custids() {
		$q = $this->query();
		$q->select(ArCust3partyFreight::aliasproperty('custid'));
		$q->distinct();
		return $q->find()->toArray();
	}

	/**
	 * Return if ArCust3partyFreight Exists
	 * @param  string $custID  Customer ID
	 * @param  string $acctnbr Account Number
	 * @return bool
	 */
	public function exists($custID, $acctnbr) {
		return boolval($this->queryCustAccount($custID, $acctnbr)->count());
	}

	/**
	 * Return if ArCust3partyFreight Customer Exists
	 * @param  string $custID  Customer ID
	 * @return bool
	 */
	public function custidExists($custID) {
		return boolval($this->queryCustid($custID)->count());
	}


	/**
	 * Return ArCust3partyFreight
	 * @param  string $custID  Customer ID
	 * @param  string $acctnbr Account Number
	 * @return ArCust3partyFreight
	 */
	public function customerAccount($custID, $acctnbr) {
		return $this->queryCustAccount($custID, $acctnbr)->findOne();
	}

	/**
	 * Return Array ready for JSON
	 * @param  Record  $record Code
	 * @return array
	 */
	public function recordJson(Record $record) {
		$json = [];
		foreach (array_keys(self::FIELD_ATTRIBUTES) as $field) {
			$json[$field] = $record->$field;
		}
		return $json;
	}

	/**
	 * Return New or Existing ArCust3partyFreight
	 * @param  string $custID  Customer ID
	 * @param  string $acctnbr Account Number
	 * @return ArCust3partyFreight
	 */
	public function getOrCreate($custID, $acctnbr) {
		if ($this->exists($custID, $acctnbr) === false) {
			return $this->new($custID, $acctnbr);
		}
		return $this->customerAccount($custID, $acctnbr);
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Freight Account
	 * @param  string $custID  Customer ID
	 * @param  string $acctnbr Account Number
	 * @return ArCust3partyFreight
	 */
	public function new($custID, $acctnbr = '') {
		$account = new ArCust3partyFreight();
		$cmm = Cmm::instance();

		if ($cmm->exists($custID)) {
			$account->setCustid($custID);
		}

		if (empty($acctnbr) === false && strtolower($acctnbr) != 'new') {
			$acctnbr = $this->wire('sanitizer')->string($acctnbr, ['maxLength' => $this->fieldAttribute('accountnbr', 'maxlength')]);
			$account->setAccountnbr($acctnbr);
		}

		// Set Default Values
		foreach (self::FIELD_ATTRIBUTES as $name => $attr) {
			if (array_key_exists('default', $attr)) {
				$setField = 'set' . ucfirst($name);
				$account->$setField($attr['default']);
			}
		}
		return $account;
	}

/* =============================================================
	CRUD Processing (UPDATE)
============================================================= */
	/**
	 * Update Record from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = [];
		$custID = $values->string('custid');
		$acctnbr = $values->string('accountnbr', ['maxLength' => $this->fieldAttribute('accountnbr', 'maxlength')]);

		$record        = $this->getOrCreate($custID, $acctnbr);
		$record->setDate(date('Ymd'));
		$record->setTime(date('His'));
		$invalidfields = $this->_inputUpdate($input, $record);
		$response      = $this->saveAndRespond($record, $invalidfields);
		$this->setResponse($response);
		return $response->hasSuccess();
	}


	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Record    $record
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Record $record) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields = [
			'nameAddress' => $this->_inputUpdateNameAddress($values, $record),
			'phones'      => $this->_inputUpdatePhones($values, $record),
		];

		$invalid = array_merge(
			$invalidfields['nameAddress'], $invalidfields['phones']
		);
		return $invalid;
	}

	/**
	 * Update Name, Address Fields
	 * @param WireInputData        $values
	 * @param ArCust3partyFreight  $account
	 * @return array
	 */
	private function _inputUpdateNameAddress(WireInputData $values, Record $account) {
		$account->setName($values->text('name', ['maxLength' => $this->fieldAttribute('name', 'maxlength')]));
		$account->setAddress1($values->text('address1', ['maxLength' => $this->fieldAttribute('address1', 'maxlength')]));
		$account->setAddress2($values->text('address2', ['maxLength' => $this->fieldAttribute('address2', 'maxlength')]));
		$account->setAddress3($values->text('address3', ['maxLength' => $this->fieldAttribute('address3', 'maxlength')]));
		$account->setCity($values->text('city', ['maxLength' => $this->fieldAttribute('city', 'maxlength')]));
		$account->setZip($values->text('zip', ['maxLength' => $this->fieldAttribute('zip', 'maxlength')]));

		$state = $values->text('state');
		$account->setState('');
		if (Codes\Misc\StateCodes::instance()->exists($state)) {
			$account->setState($state);
		}

		$country = $values->text('country');
		
		$account->setCountry('');
		if (Codes\Mar\Cocom::instance()->exists($country)) {
			$account->setCountry($country);
		}
		return [];
	}

	/**
	 * Update Name, Address Fields
	 * @param WireInputData        $values
	 * @param ArCust3partyFreight  $account
	 * @return array
	 */
	private function _inputUpdatePhones(WireInputData $values, Record $account) {
		$sanitizer = $this->sanitizer;
		$account->setInternational($values->yn('international'));
		$account->setPhone('');
		$account->setFax('');
		$account->setPhoneintl('');
		$account->setFaxintl('');

		if ($account->international === ArCust3partyFreight::INTERNATIONAL_TRUE) {
			$account->setPhoneintl($sanitizer->text(implode('', $values->array('phoneintl', ['delimiter' => '-'])), ['maxLength' => $this->fieldAttribute('phoneintl', 'maxlength')]));
			$account->setFaxintl($sanitizer->text(implode('', $values->array('faxintl', ['delimiter' => '-'])), ['maxLength' => $this->fieldAttribute('faxintl', 'maxlength')]));
		}
		$account->setPhone($sanitizer->text(implode('', $values->array('phone', ['delimiter' => '-'])), ['maxLength' => $this->fieldAttribute('phone', 'maxlength')]));
		$account->setFax($sanitizer->text(implode('', $values->array('fax', ['delimiter' => '-'])), ['maxLength' => $this->fieldAttribute('fax', 'maxlength')]));
		$account->setExtension($values->text('extension', ['maxLength' => $this->fieldAttribute('extension', 'maxlength')]));
		return [];
	}


/* =============================================================
	CRUD Processing (DELETE)
============================================================= */
	/**
	 * Delete Record
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$custID = $values->string('custid');
		$acctnbr = $values->string('accountnbr');

		if ($this->exists($custID, $acctnbr) === false) {
			$response = Response::responseSuccess("Customer $custID Freight Account $acctnbr was deleted");
			$response->buildMessage(static::RESPONSE_TEMPLATE);
			$response->setKey("$custID|$acctnbr");
			return true;
		}
		$account = $this->customerAccount($custID, $acctnbr);
		$account->delete();
		$response = $this->saveAndRespond($account);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

/* =============================================================
	Dplus Requests
============================================================= */
	/**
	 * Return Request Data Neeeded for Dplus Update
	 * @param  Record $record
	 * @return array
	 */
	protected function generateRequestData(Record $record) {
		$dplusdb = DbDplus::instance()->dbconfig->dbName;
		return ["DBNAME=$dplusdb", 'UPDATE3PARTY', "CUSTID=$record->custid", "ACCTNBR=$record->accountnbr"];
	}

/* =============================================================
	Record Locker Functions
============================================================= */
	/**
	 * Return Key for Code
	 * @param  Record   $record
	 * @return string
	 */
	public function getRecordlockerKey(Record $record) {
		return implode(FunctionLocker::glue(), [$record->custid, $record->accountnbr]);
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return Customer Name
	 * @param  string $custID
	 * @return string
	 */
	public function customerName($custID) {
		return Cmm::instance()->name($custID);
	}
}
