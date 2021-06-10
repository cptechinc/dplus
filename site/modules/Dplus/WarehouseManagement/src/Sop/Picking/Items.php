<?php namespace Dplus\Wm\Sop\Picking;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dpluso Model
use PickSalesOrderDetailQuery, PickSalesOrderDetail;

use Dplus\Wm\Base;

/**
 * Items
 * Handles the Link Between Picking and the Order Items that are available to pick
 */
class Items extends Base {
	/**
	 * Sales OrderOrder Number
	 * @var string
	 */
	protected $ordn;

	/**
	 * Sets Sales Order Number
	 * @param string $sessionID
	 */
	public function setOrdn($ordn) {
		$this->ordn = $ordn;
	}

	/**
	 * Return Sales Order Number
	 * @return string
	 */
	public function getOrdn() {
		return $this->ordn;
	}

	/**
	 * Return Query
	 * @return PickSalesOrderDetailQuery
	 */
	public function query() {
		return PickSalesOrderDetailQuery::create();
	}

	/**
	 * Return Query Filtered by Session ID, Order Number
	 * @return PickSalesOrderDetailQuery
	 */
	public function queryOrdn() {
		$q = $this->query();
		$q->filterBySessionidOrder($this->sessionID, $this->ordn);
		return $q;
	}

	/**
	 * Return Pick Order Items
	 * @return PickSalesOrderDetail[]|ObjectCollection
	 */
	public function getItems() {
		$q = $this->queryOrdn();
		return $q->find();
	}

	/**
	 * Return Pick Order Items
	 * @param  bool   $picked Return Picked ITems? (false = return only unpicked)
	 * @return PickSalesOrderDetail[]|ObjectCollection
	 */
	public function getItemsFiltered($picked = false) {
		$q = $this->queryOrdn();

		if ($picked) {
			$q->filterByQtyremaining(0);
			return $q->find();
		}
		$q->filterByQtyremaining(array('min' => 1));
		return $q->find();
	}

	/**
	 * Return PickSalesOrderDetail that matches ItemID
	 * // TODO HANDLE CHOOSING CORRECT LINE
	 *
	 * @param  string $itemID Item ID
	 * @return PickSalesOrderDetail
	 */
	public function getItemByItemid($itemID) {
		$q = PickSalesOrderDetailQuery::create();
		$q->filterBySessionidOrder($this->sessionID, $this->ordn);
		$q->filterByitemid($itemID);
		return $q->findOne();
	}

	/**
	 * Return PickSalesOrderDetail for Linenbr
	 * @param  int    $linenbr Line
	 * @return PickSalesOrderDetail
	 */
	public function getItemByLinenbr(int $linenbr) {
		$q = PickSalesOrderDetailQuery::create();
		$q->filterBySessionidOrder($this->sessionID, $this->ordn);
		$q->filterByLinenbr($linenbr);
		return $q->findOne();
	}

	/**
	 * Returns if this Picking Order has any Sublines
	 * @return bool
	 */
	public function hasSublines() {
		$q = PickSalesOrderDetailQuery::create();
		$q->filterBySessionidOrder($this->sessionID, $this->ordn);
		$q->filterBySublinenbr(0, Criteria::GREATER_THAN);
		return boolval($q->count());
	}

	/**
	 * Validates if Item ID is on the Picking Sales Order
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function hasItemid($itemID) {
		$q = PickSalesOrderDetailQuery::create();
		$q->filterBySessionidOrder($this->sessionID, $this->ordn);
		$q->filterByItemid($itemID);
		return boolval($q->count());
	}
}
