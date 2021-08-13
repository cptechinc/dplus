<?php namespace Dplus\Min\Inmain\I2i;
// Dplus Models
use InvItem2ItemQuery, InvItem2Item;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Validators
use Dplus\CodeValidators as Validators;

class I2i extends WireData {
	const MODEL              = 'InvItem2Item';
	const MODEL_KEY          = ['parentitemid', 'childitemid'];
	const DESCRIPTION        = 'Item to Item';
	const DESCRIPTION_RECORD = 'Item to Item';
	const RESPONSE_TEMPLATE  = 'Item {key} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'i2i';

	private static $instance;

	public function __construct() {
		$this->sessionID = session_id();
	}

	/**
	 * Return Instance of I2i
	 * @return I2i
	 */
	public static function getInstance() {
		if (empty(self::$instance)) {
			$i2i = new I2i();
			$i2i->init();
			self::$instance = $i2i;
		}
		return self::$instance;
	}

	/**
	 * Return Query
	 * @return InvItem2ItemQuery
	 */
	public function query() {
		return InvItem2ItemQuery::create();
	}

	/**
	 * Return Filtered Query
	 * @param  string $parentID  Parent Item ID
	 * @param  string $childID   Child Item ID
	 * @return InvItem2ItemQuery
	 */
	public function queryI2i($parentID, $childID) {
		$q = $this->query();
		$q->filterByParentitemid($parentID);
		$q->filterByChilditemid($childID);
		return $q;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return if Record Exists
	 * @param  string $parentID  Parent Item ID
	 * @param  string $childID   Child Item ID
	 * @return bool
	 */
	public function exists($parentID, $childID) {
		$q = $this->queryI2i($parentID, $childID);
		return boolval($q->count());
	}

	/**
	 * Return InvItem2Item from Database
	 * @param  string $parentID  Parent Item ID
	 * @param  string $childID   Child Item ID
	 * @return InvItem2Item
	 */
	public function xref($parentID, $childID)  {
		$q = $this->queryI2i($parentID, $childID);
		return $q->findOne();
	}

	/**
	 * Return InvItem2Item from Recordlocker Key
	 * @param  string $key Record Key in Record Locker Format
	 * @return InvItem2Item
	 */
	public function xrefFromRecordlockerKey($key) {
		$keys = explode(FunctionLocker::glue(), $key);
		return $this->xref($keys[0], $keys[1]);
	}

	/**
	 * Return new InvItem2Item
	 * @param  string $parentID  Parent Item ID
	 * @param  string $childID   Child Item ID
	 * @return InvItem2Item
	 */
	public function new($parentID = '', $childID = '') {
		$r = new InvItem2Item();
		if ($parentID && strtolower($parentID) != 'new') {
			$r->setParentitemid($parentID);
		}
		if ($childID) {
			$r->setChilditemid($childID);
		}
		$r->setDummy('P');
		return $r;
	}

	/**
	 * Return Item2Item (new or from DB)
	 * @param  string $parentID  Parent Item ID
	 * @param  string $childID   Child Item ID
	 * @return InvItem2Item
	 */
	public function getOrCreate($parentID, $childID) {
		if ($this->exists($parentID, $childID)) {
			return $this->xref($parentID, $childID);
		}
		return $this->new($parentID, $childID);
	}

/* =============================================================
	CRUD Update, Delete Functions
============================================================= */
	/**
	 * Updates Record
	 * @param  InvItem2Item  $xref   Item 2 Item Record
	 * @param  WireInput     $input  Input Data
	 * @return void
	 */
	public function updateXrefInput(InvItem2Item $xref, WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidFields = $this->updateXrefValidated($xref, $input);
		return $invalidFields;
	}

	/**
	 * Sets Record fields if valid, returns invalid fields
	 * @param  InvItem2Item  $xref Item 2 Item Record
	 * @param  WireInput     $input  Input Data
	 * @return array
	 */
	public function updateXrefValidated(InvItem2Item $xref, WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$validate = new Validators\Min();
		$invalid = array();

		$fields = array(
			'whseid' => array(
				'description' => 'Supply Warehouse ID',
				'input'       => 'whseID',
				'function'    => 'whseid'
			),
			'parentitemid' => array(
				'description' => 'Parent Item ID',
				'input'       => 'parentID',
				'function'    => 'itemid'
			),
			'childitemid' => array(
				'description' => 'Child Item ID',
				'input'       => 'childID',
				'function'    => 'itemid'
			)
		);

		$validator = $this->wire('modules')->get('InputValidator');
		$validator->set_validator($validate)->set_input($input)->set_record($xref)->set_validatefields($fields);
		$invalid = $validator->validate();
		$validator->set_valid_properties();
		return $invalid;
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
	/**
	 * Takes Input, calls function to requested action
	 * @param  WireInput $input Input
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'delete-i2i':
				return $this->inputDelete($input);
				break;
			case 'update-i2i':
				return $this->inputUpdate($input);
				break;
			default:
				$key = implode('-', [$values->text('parentID'), $values->text('childID')]);
				$message = self::DESCRIPTION_RECORD . " ($key) was not saved, no action was specified";
				$this->wire('session')->setFor('response', 'i2i', Response::response_error($key, $message));
				return false;
				break;
		}
	}

	/**
	 * Update I2i from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	public function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$parentID = $values->text('parentID');
		$childID  = $values->text('childID');
		$xref     = $this->getOrCreate($parentID, $childID);

		if ($this->lockrecord($xref) === false && $xref->isNew() === false) {
			$message = self::DESCRIPTION_RECORD . " ($xref->parentID-$xref->childID)  was not saved, it is locked by " . $this->recordlocker->getLockingUser($this->getRecordlockerKey($xref));
			$this->wire('session')->setFor('response', 'i2i', XrefResponse::response_error($this->getRecordlockerKey($xref), $message));
			return false;
		}
		$invalidFields = $this->updateXrefInput($xref, $input);
		$response = $this->saveAndRespond($xref, $invalidFields);
		$this->wire('session')->setFor('response', 'i2i', $response);
		return $this->wire('session')->getFor('response', 'i2i')->has_success();
	}

/* =============================================================
	CRUD Response Functions
============================================================= */
	/**
	 * Returns XrefResponse based on the outcome of the database save
	 * @param  InvItem2Item $xref           Record to record response of database save
	 * @param  array        $invalidfields
	 * @return XrefResponse
	 */
	protected function saveAndRespond(InvItem2Item $xref, array $invalidfields = null) {
		$is_new = $xref->isDeleted() ? false : $xref->isNew();
		$saved  = $xref->isDeleted() ? $xref->isDeleted() : $xref->save();

		$response = new Response();
		$response->set_key($this->getRecordlockerKey($xref));

		if ($saved) {
			$response->set_success(true);
		} else {
			$response->set_error(true);
		}

		if ($is_new) {
			$response->set_action(Response::CRUD_CREATE);
		} elseif ($xref->isDeleted()) {
			$response->set_action(Response::CRUD_DELETE);
		} else {
			$response->set_action(Response::CRUD_UPDATE);
		}

		$response->build_message(self::RESPONSE_TEMPLATE);

		if ($response->has_success() && empty($invalidfields)) {
			$this->updateDplusServer($xref);
		}
		return $response;
	}

/* =============================================================
	Dplus Request Functions
============================================================= */
	/**
	 * Writes File for Dplus to update the VXM file for this ITEM
	 * @param  InvItem2Item $item
	 * @return void
	 */
	public function updateDplusServer(InvItem2Item $xref) {
		$config = $this->wire('config');
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		$data = ["DBNAME=$dplusdb", 'UPDATEI2I', "MSTRITEM=$xref->parentitemid", "CHILDITEM=$xref->childitemid"];

		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
	}

/* =============================================================
	Hook Functions
============================================================= */
	/**
	 * Set up Functions / Properties for pw_templated pages
	 * @return void
	 */
	public function init() {
		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(self::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
	}

	/**
	 * Return Key for InvItem2Item
	 * @param  InvItem2Item $xref X-Ref
	 * @return string
	 */
	public function getRecordlockerKey(InvItem2Item $xref) {
		return implode(FunctionLocker::glue(), [$xref->parentitemid, $xref->childitemid]);
	}

	/**
	 * Lock Record, validate User is locking Record
	 * @param  InvItem2Item $xref
	 * @return bool
	 */
	public function lockrecord($xref) {
		$key = $this->getRecordlockerKey($xref);
		if ($this->recordlocker->isLocked($key) === false) {
			$this->recordlocker->lock($key);
		}
		return $this->recordlocker->userHasLocked($key);
	}
}
