<?php namespace Dplus\Wm\Inventory;
// Propel ORM Library
use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use InvWhseLotQuery, InvWhseLot;
// Dplus WarehouseManagment
use Dplus\Wm\Base;

/**
 * FindItem
 * 
 * Service for Locating Items, Lotserials
 */
class FindItem extends Base {
	private static $instance;

	private $whseid;

	/** @return self */
	public static function instance() {
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Set Warehouse ID for Inquiry Calls
	 * @param  string $whseID  Warehouse ID
	 * @return void
	 */
	public function setWhseid($whseID) {
		$this->whseid = $whseID;
	}

/* =============================================================
	Query functions
============================================================= */
	/**
	 * Return Query
	 * @return InvWhseLotQuery
	 */
	public function query() {
		return InvWhseLotQuery::create();
	}

	/**
	 * Return Query filtered by Warehouse ID
	 * @return InvWhseLotQuery
	 */
	public function queryWhseid() {
		if (empty($this->whseid)) {
			return $this->query();
		}
		$q = $this->query();
		$q->filterByWhseid($this->whseid);
		return $q;
	}

/* =============================================================
	Query functions
============================================================= */
	/**
	 * Return Distinct Items based off InvSearch
	 * @return InvWhseLot[]|ObjectCollection
	 */
	public function distinctItemsFromInvsearch() {
		$q = $this->queryWhseid();
		$q->filterByItemid(Search::instance()->getDistinctItemids());
		$q->groupByItemid();
		return $q->find();
	}

	/**
	 * Return the number of Lotserials for Item ID
	 * @param  string $itemID  Item ID
	 * @return int
	 */
	public function countLotserials($itemID) {
		$q = $this->queryWhseid();
		$q->filterByItemid($itemID);
		return $q->count();
	}

	/**
	 * Return Total Item Qty
	 * @param  string $itemID  Item ID
	 * @return float
	 */
	public function totalItemQty($itemID) {
		$colQty = InvWhseLot::aliasproperty('qty');

		$q = $this->queryWhseid();
		$q->filterByItemid($itemID);
		$q->withColumn("SUM($colQty)", 'qty');
		$q->select('qty');
		return $q->findOne();
	}

	/**
	 * Return the number of Bins
	 * @param  string $itemID  Item ID
	 * @return float
	 */
	public function countDistinctBins($itemID) {
		$colBinid = InvWhseLot::aliasproperty('binid');

		$q = $this->queryWhseid();
		$q->filterByItemid($itemID);
		$q->withColumn("COUNT(DISTINCT($colBinid))", 'bincount');
		$q->select('bincount');
		return $q->findOne();
	}

	/**
	 * Return the number of Bins
	 * @param  string $itemID  Item ID
	 * @return float
	 */
	public function countDistinctLotserials($itemID) {
		$colLotserial = InvWhseLot::aliasproperty('lotserial');

		$q = $this->queryWhseid();
		$q->filterByItemid($itemID);
		$q->withColumn("COUNT(DISTINCT($colLotserial))", 'lotcount');
		$q->select('lotcount');
		return $q->findOne();
	}

	/**
	 * Return Lotserials Found for Item ID
	 * @param  string $itemID  Item ID
	 * @return InvWhseLot[]|ObjectCollection
	 */
	public function lotserials($itemID) {
		$q = $this->queryWhseid();
		$q->filterByItemid($itemID);
		return $q->find();
	}

	/**
	 * Return Lotserials Found for Item ID ordered by Bin ID
	 * @param  string $itemID  Item ID
	 * @return InvWhseLot[]|ObjectCollection
	 */
	public function lotserialsOrderedByBinid($itemID) {
		$q = $this->queryWhseid();
		$q->filterByItemid($itemID);
		$q->orderByBinid();
		return $q->find();
	}

	/**
	 * Return Lotserials Found for Item ID mathcing lotserials found in invsearch ordered by Bin ID
	 * @param  string $itemID  Item ID
	 * @return InvWhseLot[]|ObjectCollection
	 */
	public function lotserialsMatchingInvsearch($itemID) {
		$q = $this->queryWhseid();
		$q->filterByItemid($itemID);
		$q->filterByLotserial(Search::instance()->getDistinctLotserialsArray());
		$q->orderByBinid();
		return $q->find();
	}

/* =============================================================
	Dplus Request functions
============================================================= */
	/**
	 * Request Search
	 * @param  string $q Query
	 * @return void
	 */
	public function requestSearch($q) {
		$q = strtoupper($q);
		$data = array('INVSEARCH', "QUERY=$q");
		$this->sendDplusRequest($data);
	}
}