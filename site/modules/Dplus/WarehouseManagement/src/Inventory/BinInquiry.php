<?php namespace Dplus\Wm\Inventory;
// Propel ORM Library
use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use InvWhseLotQuery, InvWhseLot;
// Dplus WarehouseManagment
use Dplus\Wm\Base;

/**
 * BinInquiry
 * 
 * Service for Returning Bin Contents
 */
class BinInquiry extends Base {
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

	/**
	 * Return Query filtered by Bin ID
	 * @param  string|array $binID
	 * @return InvWhseLotQuery
	 */
	public function queryBinid($binID = null) {
		if (empty($binID)) {
			return $this->queryWhseid();
		}
		$q = $this->queryWhseid();
		$q->filterByBinid($binID);
		return $q;
	}

/* =============================================================
	Query functions
============================================================= */
	/**
	 * Return Distinct Items for Bin(s)
	 * @param  string|array $binID
	 * @return InvWhseLot[]|ObjectCollection
	 */
	public function distinctItems($binID = null) {
		$q = $this->queryBinid($binID);
		$q->groupByItemid();
		return $q->find();
	}

	/**
	 * Return Total Item Qty in Bin
	 * @param  string $binID   Bin ID
	 * @param  string $itemID  Item ID
	 * @return float
	 */
	public function totalBinItemQty($binID, $itemID) {
		$colQty = InvWhseLot::aliasproperty('qty');

		$q = $this->queryBinid($binID);
		$q->filterByItemid($itemID);
		$q->withColumn("SUM($colQty)", 'qty');
		$q->select('qty');
		return $q->findOne();
	}

	/**
	 * Return Item Lotserials found in Bin
	 * @param  string $binID   Bin ID
	 * @param  string $itemID  Item ID
	 * @return InvWhseLot[]|ObjectCollection
	 */
	public function lotserials($binID, $itemID) {
		$q = $this->queryBinid($binID);
		$q->filterByItemid($itemID);
		return $q->find();
	}

	/**
	 * Return the number Item Lotserials found in Bin
	 * @param  string $binID   Bin ID
	 * @param  string $itemID  Item ID
	 * @return int
	 */
	public function countLotserials($binID, $itemID) {
		$q = $this->queryBinid($binID);
		$q->filterByItemid($itemID);
		return $q->count();
	}

/* =============================================================
	Dplus Request functions
============================================================= */
	/**
	 * Send Request for InvSearch records
	 * @param  string $binID   Bin ID
	 * @return void
	 */
	public function requestBinInventory($binID) {
		$binID = $this->sanitizer->binID($binID);
		$data = ['BININQUIRY', "BINID=$binID"];
		$this->sendDplusRequest($data);
	}

}