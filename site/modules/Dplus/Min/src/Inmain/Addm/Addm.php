<?php namespace Dplus\Min\Inmain\Addm;
// Dplus Models
use ItemAddonItemQuery, ItemAddonItem;
use WarehouseQuery, Warehouse;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Validators
use Dplus\CodeValidators as Validators;

class Addm extends WireData {
	const MODEL              = 'ItemAddonItem';
	const MODEL_KEY          = ['itemid', 'addonitemid'];
	const DESCRIPTION        = 'Add-on Item';
	const DESCRIPTION_RECORD = 'Add-on Item';
	const RESPONSE_TEMPLATE  = 'Item {itemID} Add-on {addonID} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'addm';

	private static $instance;

	public function __construct() {
		$this->sessionID = session_id();
	}

	/**
	 * Return Instance of Addm
	 * @return Addm
	 */
	public static function getInstance() {
		if (empty(self::$instance)) {
			$i2i = new Addm();
			$i2i->init();
			self::$instance = $i2i;
		}
		return self::$instance;
	}

	/**
	 * Return Query
	 * @return ItemAddonItemQuery
	 */
	public function query() {
		return ItemAddonItemQuery::create();
	}

	/**
	 * Return Filtered Query
	 * @param  string $itemID  Item ID
	 * @param  string $addonID Add-on Item ID
	 * @return ItemAddonItemQuery
	 */
	public function queryAddm($itemID, $addonID) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterByAddonitemid($addonID);
		return $q;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return if Record Exists
	 * @param  string $itemID  Item ID
	 * @param  string $addonID Add-on Item ID
	 * @return bool
	 */
	public function exists($itemID, $addonID) {
		$q = $this->queryAddm($itemID, $addonID);
		return boolval($q->count());
	}

	/**
	 * Return If ItemAddonItem exists
	 * @param  string $key Record Key in Record Locker Format
	 * @return bool
	 */
	public function existsFromRecordlockerKey($key) {
		$keys = explode(FunctionLocker::glue(), $key);
		return $this->exists($keys[0], $keys[1]);
	}

	/**
	 * Return ItemAddonItem from Database
	 * @param  string $itemID  Item ID
	 * @param  string $addonID Add-on Item ID
	 * @return ItemAddonItem
	 */
	public function xref($itemID, $addonID)  {
		$q = $this->queryAddm($itemID, $addonID);
		return $q->findOne();
	}

	/**
	 * Return ItemAddonItem from Recordlocker Key
	 * @param  string $key Record Key in Record Locker Format
	 * @return ItemAddonItem
	 */
	public function xrefFromRecordlockerKey($key) {
		$keys = explode(FunctionLocker::glue(), $key);
		return $this->xref($keys[0], $keys[1]);
	}

	/**
	 * Return new ItemAddonItem
	 * @param  string $itemID  Item ID
	 * @param  string $addonID Add-on Item ID
	 * @return ItemAddonItem
	 */
	public function new($itemID = '', $addonID = '') {
		$r = new ItemAddonItem();
		if ($itemID && strtolower($itemID) != 'new') {
			$r->setItemid($itemID);
		}
		if ($addonID && strtolower($addonID) != 'new') {
			$r->setAddonitemid($addonID);
		}
		$r->setDummy('P');
		return $r;
	}

	/**
	 * Return Item2Item (new or from DB)
	 * @param  string $itemID  Item ID
	 * @param  string $addonID Add-on Item ID
	 * @return ItemAddonItem
	 */
	public function getOrCreate($itemID, $addonID) {
		if ($this->exists($itemID, $addonID)) {
			return $this->xref($itemID, $addonID);
		}
		return $this->new($itemID, $addonID);
	}

/* =============================================================
	CRUD Update, Delete Functions
============================================================= */
	/**
	 * Updates Record
	 * @param  ItemAddonItem  $xref   Item 2 Item Record
	 * @param  WireInput     $input  Input Data
	 * @return void
	 */
	public function updateXrefInput(ItemAddonItem $xref, WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidFields = $this->updateXrefValidated($xref, $input);
		$xref->setDate(date('Ymd'));
		$xref->setTime(date('His'));
		return $invalidFields;
	}

	/**
	 * Sets Record fields if valid, returns invalid fields
	 * @param  ItemAddonItem  $xref Item 2 Item Record
	 * @param  WireInput     $input  Input Data
	 * @return array
	 */
	public function updateXrefValidated(ItemAddonItem $xref, WireInput $input) {
		$rm       = strtolower($input->requestMethod());
		$values   = $input->$rm;
		$validate = new Validators\Min();
		$invalid  = [];

		$fields = [
			'itemID' => [
				'description' => 'Item ID',
				'input'       => 'itemID',
				'function'    => 'itemid'
			],
			'addonID' => [
				'description' => 'Add-on Item',
				'input'       => 'addonID',
				'function'    => 'itemid'
			]
		];

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
			case 'delete':
				return $this->inputDelete($input);
				break;
			case 'update':
				return $this->inputUpdate($input);
				break;
			default:
				$key = implode('-', [$values->text('itemID'), $values->text('addonID')]);
				$message = self::DESCRIPTION_RECORD . " ($key) was not saved, no action was specified";
				$this->wire('session')->setFor('response', 'i2i', Response::response_error($key, $message));
				return false;
				break;
		}
	}

	/**
	 * Update Addm from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	public function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->text('parentID');
		$addonID  = $values->text('childID');
		$xref     = $this->getOrCreate($itemID, $addonID);

		if ($this->lockrecord($xref) === false && $xref->isNew() === false) {
			$message = self::DESCRIPTION_RECORD . " ($xref->parentID-$xref->childID)  was not saved, it is locked by " . $this->recordlocker->getLockingUser($this->getRecordlockerKey($xref));
			$this->wire('session')->setFor('response', 'i2i', Response::response_error($this->getRecordlockerKey($xref), $message));
			return false;
		}
		$invalidFields = $this->updateXrefInput($xref, $input);
		$response = $this->saveAndRespond($xref, $invalidFields);
		$this->wire('session')->setFor('response', 'i2i', $response);
		return $this->wire('session')->getFor('response', 'i2i')->hasSuccess();
	}

	/**
	 * Delete Addm from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->text('parentID');
		$addonID  = $values->text('childID');

		if ($this->exists($itemID, $addonID)) {
			$xref = $this->xref($itemID, $addonID);

			if ($this->lockrecord($xref) === false) {
				$key = $this->getRecordlockerKey($xref);
				$message = self::DESCRIPTION_RECORD . " ($key)  was not saved, it is locked by " . $this->recordlocker->getLockingUser($xref);
				$this->setResponse(Response::response_error($key, $message));
				return false;
			}
			$xref->delete();
			$response = $this->saveAndRespond($xref);
			$this->setResponse($response);
			return $response->hasSuccess();
		}
		return true;
	}

/* =============================================================
	CRUD Response Functions
============================================================= */
	/**
	 * Returns Response based on the outcome of the database save
	 * @param  ItemAddonItem $xref           Record to record response of database save
	 * @param  array        $invalidfields
	 * @return Response
	 */
	protected function saveAndRespond(ItemAddonItem $xref, array $invalidfields = null) {
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

		if ($response->hasSuccess() && empty($invalidfields)) {
			$this->updateDplusServer($xref);
		}
		return $response;
	}

	/**
	 * Set Session Response
	 * @param Response $response
	 */
	protected function setResponse(Response $response) {
		$this->wire('session')->setFor('response', 'addm', $response);
	}

	/**
	 * Get Session Response
	 * @return Response|null
	 */
	public function getResponse() {
		return $this->wire('session')->getFor('response', 'addm');
	}

	/**
	 * Delete Response
	 * @return void
	 */
	public function deleteResponse() {
		return $this->wire('session')->removeFor('response', 'addm');
	}

	/**
	 * Return if Field has Error
	 * NOTE: Uses $session->response_itm->fields to derive this
	 * @param  string $inputname Input name e.g. commissiongroup
	 * @return bool
	 */
	public function fieldHasError($inputname) {
		$response = $this->getResponse();
		return ($response) ? array_key_exists($inputname, $response->fields) : false;
	}

/* =============================================================
	Dplus Request Functions
============================================================= */
	/**
	 * Writes File for Dplus to update the VXM file for this ITEM
	 * @param  ItemAddonItem $item
	 * @return void
	 */
	public function updateDplusServer(ItemAddonItem $xref) {
		$config = $this->wire('config');
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		$data = ["DBNAME=$dplusdb", 'UPDATEI2I', "MSTRITEM=$xref->itemid", "CHILDITEM=$xref->addonitemid"];

		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return Warehouses to Select from
	 * @return Warehouse[]|ObjectCollection
	 */
	public function getWarehouses() {
		return WarehouseQuery::create()->find();
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
	 * Return Key for ItemAddonItem
	 * @param  ItemAddonItem $xref X-Ref
	 * @return string
	 */
	public function getRecordlockerKey(ItemAddonItem $xref) {
		return implode(FunctionLocker::glue(), [$xref->itemid, $xref->addonitemid]);
	}

	/**
	 * Return Recordlocker Key for ItemAddonItem
	 * @param  string $itemID  Item ID
	 * @param  string $addonID Add-on Item ID
	 * @return string
	 */
	public function getRecordlockerKeyFromKeys($itemID, $addonID) {
		return implode(FunctionLocker::glue(), [$itemID, $addonID]);
	}

	/**
	 * Lock Record, validate User is locking Record
	 * @param  ItemAddonItem $xref
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
