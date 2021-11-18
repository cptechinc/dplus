<?php namespace Dplus\Wm\Inventory\Whse\Lots;
// Dplus Models
use WhseLotserialQuery, WhseLotserial;
// ProcessWire
use ProcessWire\WireData;

/**
 * WhseInventory
 * Class for filtering querying Whse Lots
 */
class Lookup extends WireData {
	private static $instance;
	private $whseID;

/* =============================================================
	Setter Functions
============================================================= */
	public function setWhseID($whseID) {
		$this->whseID = $whseID;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query filtered By Sessionid
	 * @return WhseLotserialQuery
	 */
	public function query() {
		$q = WhseLotserialQuery::create();
		return $q;
	}

	/**
	 * Return Query Filtered By Warehouse ID if set
	 * @return WhseLotserialQuery
	 */
	public function queryWhse() {
		$q = $this->query();

		if (empty($this->whseID) === false) {
			$q->filterByWhse($this->whseID);
		}
	}

	/**
	 * Return Query
	 * @return WhseLotserialQuery
	 */
	public function queryWhseBins() {
		return $this->queryWhse();
	}
}
