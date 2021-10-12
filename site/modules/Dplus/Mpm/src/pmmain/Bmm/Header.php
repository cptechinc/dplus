<?php namespace Dplus\Mpm\Pmmain\Bmm;
// Dplus Models
use BomItemQuery, BomItem;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
use Dplus\Mpm\Pmmain\Bmm;

/**
 * BoM Header Manager
 * Handles CRUD requests
 */
class Header extends WireData {
	const MODEL              = 'BomItem';
	const MODEL_KEY          = 'itemid';
	const DESCRIPTION        = 'BoM Header';
	const DESCRIPTION_RECORD = 'BoM Header';
	const RESPONSE_TEMPLATE  = 'BoM {itemid} {not} {crud}';

	public function __construct() {
		$this->sessionID = session_id();
		$this->recordlocker = Bmm::getRecordLocker();
	}

/* =============================================================
	Queries
============================================================= */
	/**
	 * Return Query
	 * @return BomItemQuery
	 */
	public function query() {
		return BomItemQuery::create();
	}

	/**
	 * Return Query Filtered By Itemid, Level
	 * @param  string $itemID Item ID
	 * @param  int    $level  BoM Level
	 * @return BomItemQuery
	 */
	public function queryHeader($itemID, $level = 1) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterByLevel($level);
		return $q;
	}

/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Return if BomItem exists
	 * @param  string $itemID Item ID
	 * @param  int    $level  BoM Level
	 * @return bool
	 */
	public function exists($itemID, $level = 1) {
		$q = $this->queryHeader($itemID, $level);
		return boolval($q->count());
	}

	/**
	 * Return BomItem
	 * @param  string $itemID Item ID
	 * @param  int    $level  BoM Level
	 * @return BomItem
	 */
	public function header($itemID, $level = 1) {
		$q = $this->queryHeader($itemID, $level);
		return $q->findOne();
	}

	/**
	 * Return New or Existing BomItem
	 * @param  string $itemID Item ID
	 * @param  int    $level  BoM Level
	 * @return BomItem
	 */
	public function getOrCreate($itemID, $level = 1) {
		if ($this->exists($itemID, $level)) {
			return $this->header($itemID, $level);
		}
		return $this->new($itemID, $level);
	}

/* =============================================================
	CRUD Create
============================================================= */
	/**
	 * Return New BomItem
	 * @param  string $itemID Item ID
	 * @param  int    $level  BoM Level
	 * @return BomItem
	 */
	public function new($itemID, $level = 1) {
		$bom = new BomItem();
		$bom->setItemid($itemID);
		$bom->setLevel($level);
		$bom->setDummy('P');
		return $bom;
	}

	public function createHeader($itemID, $level = 1) {
		$bom = $this->new($itemID, $level);
		$bom->setDate(date('Ymd'));
		$bom->setTime(date('His'));
		$response = $this->saveAndRespond($bom);
		return $response->hasSuccess();
	}

/* =============================================================
	CRUD Response Functions
============================================================= */
	/**
	 * Returns ItmResponse based on the outcome of the database save
	 * @param  BomItem $bom        Record to record response of database save
	 * @param  array        $errors           Input fields that require attention
	 * @return Response
	 */
	private function saveAndRespond(BomItem $bom, array $errors = []) {
		$is_new = $bom->isDeleted() ? false : $bom->isNew();
		$saved  = $bom->isDeleted() ? $bom->isDeleted() : $bom->save();

		$locker = Bmm::getRecordLocker();
		$response = new Response();
		$response->bomID = $bom->itemid;
		$response->setKey($bom->itemid);

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($bom->isDeleted()) {
			$response->setAction(Response::CRUD_DELETE);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}
		$response->buildMessage(self::RESPONSE_TEMPLATE);
		$response->setFields($errors);

		if ($response->hasSuccess() && empty($errors)) {
			$this->requestUpdateHeader($bom->itemid);
		}
		return $response;
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	/**
	 * Send Request Data to Dplus
	 * @param  array  $data
	 * @return void
	 */
	private function requestDplus(array $data) {
		$config  = $this->wire('config');
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		$data = array_merge(["DBNAME=$dplusdb"], $data);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
	}

	/**
	 * Writes File for Dplus to update the ITM file for this ITEM
	 * @param  string $itemID Item ID
	 * @return void
	 */
	public function requestUpdateHeader($bomID) {
		$data = array('UPDATEBMM', "ITEMID=$bomID");
		$this->requestDplus($data);
	}

/* =============================================================
	RecordLocker
============================================================= */
	/**
	 * Lock Record
	 * @param  string $bomID BoM Header Item ID
	 * NOTE: Keep public so it can be used by Itm\Xrefs\Bom
	 * @return bool
	 */
	public function lockrecord($bomID) {
		if ($this->exists($bomID) === false) {
			return false;
		}
		if ($this->recordlocker->islocked($bomID) && $this->recordlocker->userHasLocked($bomID) === false) {
			return false;
		}
		if ($this->recordlocker->userHasLocked($bomID)) {
			return true;
		}
		return $this->recordlocker->lock($bomID);
	}
}
