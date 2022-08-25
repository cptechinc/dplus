<?php namespace Dplus\Mar\Armain;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Models
use ArCust3partyFreightQuery, ArCust3partyFreight;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Validators
use Dplus\CodeValidators as Validators;
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
		return $this->query()->filterByCustId($custID);
	}

	/**
	 * Return Query Filtered by Customer ID, Account Number
	 * @param  string $custID  Customer ID
	 * @param  string $acctnbr Account Number
	 * @return ArCust3partyFreightQuery
	 */
	public function queryCustAccount($custID, $acctnbr) {
		return $this->query()->filterByCustId($custID)->filterByAccountnbr($acctnbr);
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
		$json = [
			'custid'      => $record->custid,
			'accountnbr'  => $record->accountnbr,
		];
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
			$acctnbr = $this->wire('sanitizer')->text($acctnbr, ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);
			$account->setId($acctnbr);
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
		$id     = $values->text('id', ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);
		$invalidfields = [];
		$custID = $values->text('custID');
		$acctnbr = $values->text('accountnbr');

		$record        = $this->getOrCreate($custID, $acctnbr);
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

		// $invalidfields = [
		// 	'nameAddress' => $this->_inputUpdateNameAddress($input, $record),
		// 	'warehouses'  => $this->_inputUpdateWarehouses($input, $record),
		// 	'salesreps'   => $this->_inputUpdateSalespersons($input, $record),
		// 	'taxes'       => $this->_inputUpdateTaxes($input, $record),
		// 	'arcodes'     => $this->_inputUpdateArcodes($input, $record),
		// 	'ordering'    => $this->_inputUpdateOrdering($input, $record),
		// ];

		// $invalid = array_merge(
		// 	$invalidfields['nameAddress'],
		// 	$invalidfields['warehouses'],
		// 	$invalidfields['salesreps'],
		// 	$invalidfields['taxes'],
		// 	$invalidfields['arcodes'],
		// 	$invalidfields['ordering']
		// );
		// return $invalid;
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
		$custID = $values->text('custID');
		$acctnbr = $values->text('accountnbr');

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
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		$table   = static::DPLUS_TABLE;
		return ["DBNAME=$dplusdb", 'UPDATECODETABLE', "TABLE=$table", "CODE=$record->id"];
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
		return implode(FunctionLocker::glue(), [$record->id]);
	}

/* =============================================================
	Supplemental
============================================================= */
	
}
