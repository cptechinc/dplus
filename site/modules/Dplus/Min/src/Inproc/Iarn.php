<?php namespace Dplus\Min\Inproc\Iarn;
// Dplus Models
use InvAdjustmentReasonQuery, InvAdjustmentReason;
use WarehouseQuery, Warehouse;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Validators
use Dplus\CodeValidators as Validators;

class Iarn extends WireData {
	const MODEL              = 'InvAdjustmentReason';
	const MODEL_KEY          = ['code'];
	const DESCRIPTION        = 'Inventory Adjustment Reason';
	const DESCRIPTION_RECORD = 'Inventory Adjustment Reason';
	const RESPONSE_TEMPLATE  = 'Reason {key} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'iarn';

	private static $instance;

	public function __construct() {
		$this->sessionID = session_id();
	}

	/**
	 * Return Instance of Iarn
	 * @return Iarn
	 */
	public static function getInstance() {
		if (empty(self::$instance)) {
			$iarn = new Iarn();
			$iarn->init();
			self::$instance = $iarn;
		}
		return self::$instance;
	}

	/**
	 * Return Query
	 * @return InvAdjustmentReasonQuery
	 */
	public function query() {
		return InvAdjustmentReasonQuery::create();
	}

	/**
	 * Return Filtered Query
	 * @param  string $id  Reason Code
	 * @return InvAdjustmentReasonQuery
	 */
	public function queryId($id) {
		$q = $this->query();
		$q->filterById($id);
		return $q;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return if Record Exists
	 * @param  string $id  Reason Code
	 * @return bool
	 */
	public function exists($id) {
		$q = $this->queryId($id);
		return boolval($q->count());
	}

	/**
	 * Return InvAdjustmentReason from Database
	 * @param  string $id  Reason Code
	 * @return InvAdjustmentReason
	 */
	public function code($id)  {
		$q = $this->queryId($id);
		return $q->findOne();
	}

	/**
	 * Return new InvAdjustmentReason
	 * @param  string $id  Reason Code
	 * @return InvAdjustmentReason
	 */
	public function new($id) {
		$r = new InvAdjustmentReason();
		if ($id && strtolower($id) != 'new') {
			$r->setId($code);
		}
		$r->setDummy('P');
		return $r;
	}

	/**
	 * Return InvAdjustmentReason (new or from DB)
	 * @param  string $id  Reason Code
	 * @return InvAdjustmentReason
	 */
	public function getOrCreate($id) {
		if ($this->exists($id)) {
			return $this->code($id);
		}
		return $this->new($id);
	}

/* =============================================================
	CRUD Update, Delete Functions
============================================================= */
	/**
	 * Updates Record
	 * @param  InvAdjustmentReason  $xref   Inv Adjustment Reason Record
	 * @param  WireInput            $input  Input Data
	 * @return void
	 */
	public function updateXrefInput(InvAdjustmentReason $xref, WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidFields = $this->updateXrefValidated($xref, $input);
		$xref->setDate(date('Ymd'));
		$xref->setTime(date('His'));
		return $invalidFields;
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
			case 'delete-iarn':
				return $this->inputDelete($input);
				break;
			case 'update-iarn':
				return $this->inputUpdate($input);
				break;
			default:
				$id = $values->text('id');
				$message = self::DESCRIPTION_RECORD . " ($id) was not saved, no action was specified";
				$this->wire('session')->setFor('response', 'iarn', Response::response_error($key, $message));
				return false;
				break;
		}
	}

	/**
	 * Update Iarn from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	public function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->text('id');
		$reason = $this->getOrCreate($id);

		if ($this->lockrecord($id) === false && $reason->isNew() === false) {
			$message = self::DESCRIPTION_RECORD . " ($id)  was not saved, it is locked by " . $this->recordlocker->getLockingUser($id);
			$this->wire('session')->setFor('response', 'iarn', Response::response_error($id, $message));
			return false;
		}
		$invalidFields = $this->updateXrefInput($reason, $input);
		$response = $this->saveAndRespond($reason, $invalidFields);
		$this->wire('session')->setFor('response', 'iarn', $response);
		return $this->wire('session')->getFor('response', 'iarn')->has_success();
	}

	/**
	 * Delete Iarn from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->text('id');

		if ($this->exists($id)) {
			$reason = $this->code($id);

			if ($this->lockrecord($idf) === false) {
				$message = self::DESCRIPTION_RECORD . " ($id)  was not saved, it is locked by " . $this->recordlocker->getLockingUser($id);
				$this->wire('session')->setFor('response', 'iarn', Response::response_error($id, $message));
				return false;
			}
			$reason->delete();
			$response = $this->saveAndRespond($reason);
			$this->wire('session')->setFor('response', 'iarn', $response);
			return $response->has_success();
		}
		return true;
	}

/* =============================================================
	CRUD Response Functions
============================================================= */
	/**
	 * Returns Response based on the outcome of the database save
	 * @param  InvAdjustmentReason  $reason         Record to record response of database save
	 * @param  array                $invalidfields
	 * @return Response
	 */
	protected function saveAndRespond(InvAdjustmentReason $reason, array $invalidfields = null) {
		$is_new = $reason->isDeleted() ? false : $reason->isNew();
		$saved  = $reason->isDeleted() ? $reason->isDeleted() : $reason->save();

		$response = new Response();
		$response->set_key($reason->id);

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
			$this->updateDplusServer($reason);
		}
		return $response;
	}

/* =============================================================
	Dplus Request Functions
============================================================= */
	/**
	 * Writes File for Dplus to update the VXM file for this ITEM
	 * @param  InvAdjustmentReason $reason
	 * @return void
	 */
	public function updateDplusServer(InvAdjustmentReason $reason) {
		$config = $this->wire('config');
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		$data = ["DBNAME=$dplusdb", 'UPDATECODETABLE', "TABLE=IARN", "CODE=$reason->code"];

		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
	}

/* =============================================================
	Supplemental Functions
============================================================= */

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

/* =============================================================
	Recordlocker Functions
============================================================= */
	/**
	 * Lock Record, validate User is locking Record
	 * @param  string $code Reason code
	 * @return bool
	 */
	public function lockrecord($code) {
		if ($this->recordlocker->isLocked($code) === false) {
			$this->recordlocker->lock($code);
		}
		return $this->recordlocker->userHasLocked($code);
	}
}
