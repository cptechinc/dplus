<?php namespace Dplus\Wm\Inventory\Whse\Lots;
// Dplus Models
use InvWhseLotQuery, InvWhseLot;
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
	 * @return InvWhseLotQuery
	 */
	public function query() {
		$q = InvWhseLotQuery::create();
		return $q;
	}

	/**
	 * Return Query Filtered By Warehouse ID if set
	 * @return InvWhseLotQuery
	 */
	public function queryWhse() {
		$q = $this->query();

		if (empty($this->whseID) === false) {
			$q->filterByWhse($this->whseID);
		}
		return $q;
	}

	/**
	 * Return Query
	 * @return InvWhseLotQuery
	 */
	public function queryWhseBins() {
		return $this->queryWhse();
	}

/* =============================================================
	Lookup Functions
============================================================= */
	/**
	 * Return Bins for Item ID
	 * @param  array|string $itemID Item ID
	 * @return array
	 */
	public function getDistinctBinsByItemid($itemID) {
		$q = $this->queryWhseBins();
		$q->filterByItemid($itemID);
		$q->select(InvWhseLot::aliasproperty('bin'));
		$q->groupBy(InvWhseLot::aliasproperty('bin'));
		return $q->find()->toArray();
	}
}
