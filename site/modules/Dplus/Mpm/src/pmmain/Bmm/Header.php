<?php namespace Dplus\Mpm\Pmmain\Bmm;
// Dplus Models
use BomItemQuery, BomItem;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;

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
	 * Return BomItem
	 * @param  string $itemID Item ID
	 * @param  int    $level  BoM Level
	 * @return BomItem
	 */
	public function header($itemID, $level = 1) {
		$q = $this->queryHeader($itemID, $level);
		return $q->findOne();
	}
}
