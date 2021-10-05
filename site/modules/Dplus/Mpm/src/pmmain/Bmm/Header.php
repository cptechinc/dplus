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
