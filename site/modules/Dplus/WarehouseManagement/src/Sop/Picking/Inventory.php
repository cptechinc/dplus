<?php namespace Dplus\Wm\Sop\Picking;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
use InvLotQuery, InvLot;
// Dpluso Model
use PickSalesOrderDetailQuery, PickSalesOrderDetail;
use WhseitemphysicalcountQuery, Whseitemphysicalcount;
// Dplus Picking
use Dplus\Wm\Sop\Picking\Strategies\Inventory\Lookup as InventoryLookups;
// Dplus Wm
use Dplus\Wm\Base;

/**
 * Inventory
 * Class that Handles the link to the Inventory for Picking
 */
class Inventory extends Base {
	public $lookup;

	public function __construct() {
		parent::__construct();
		$this->lookup = new InventoryLookups\ExcludePackBin();
	}

	/**
	 * Set Session ID for this and Inventory Lookup
	 * @param string $sessionID
	 */
	public function setSessionID($sessionID = '') {
		parent::setSessionID($sessionID);
		$this->lookup->setSessionId($this->sessionID);
	}

	/**
	 * Return Inventory Items
	 * @param  string $scan        Scan
	 * @param  bool   $includepack Include Pack Bin?
	 * @return Whseitemphysicalcount[]|ObjectCollection
	 */
	public function getResults($scan) {
		return $this->lookup->getResults($scan);
	}

	/**
	 * Returns if Item is Serialized
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function isItemSerialized($itemID) {
		$q = ItemMasterItemQuery::create();
		return $q->is_item_serialized($itemID);
	}

	/**
	 * Returns if Item is Lotted
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function isItemLotted($itemID) {
		$q = ItemMasterItemQuery::create();
		return $q->is_item_lotted($itemID);
	}

	/**
	 * Returns if Item is Serialized
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function isItemLotSerialized($itemID) {
		$q = ItemMasterItemQuery::create();
		return $q->is_item_serialized($itemID) || $q->is_item_lotted($itemID);
	}

	/**
	 * Returns if Item is Lot Serial
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function isItemNormal($itemID) {
		$q = ItemMasterItemQuery::create();
		return $q->is_item_normal($itemID);
	}

	/**
	 * Return Item Master Item
	 * @param  string $itemID Item ID
	 * @return ItemMasterItem
	 */
	public function getItmItem($itemID) {
		return ItemMasterItemQuery::create()->findOneByItemid($itemID);
	}

	/**
	 * Return Inv Lot
	 * @param  string $itemID    Item ID
	 * @param  string $lotserial Lot / Serial #
	 * @param  string $binID     Bin ID
	 * @return InvLot
	 */
	public function getInvlot($itemID, $lotserial, $binID) {
		$q = InvLotQuery::create();
		$q->filterByWhse($this->getWhsesession()->whseid);
		$q->filterByItemid($itemID);
		$q->filterByLotserial($lotserial);
		$q->filterByBin($binID);
		return $q->findOne();
	}

	/**
	 * Send request to Create Ilookup Reference for Item ID
	 * @return void
	 */
	public function requestSearch($ordn, $q) {
		$data = ['PICKSEARCH', "QUERY=$q", "ORDERNBR=$ordn"];
		$this->sendDplusRequest($data);
	}
}
