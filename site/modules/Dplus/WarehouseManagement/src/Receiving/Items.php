<?php namespace Dplus\Wm\Receiving;

use Purl\Url;
// ProcessWire
use ProcessWire\WireData;
// Dplus Online Models
use PurchaseOrderQuery, PurchaseOrder;
use PurchaseOrderDetailReceivingQuery, PurchaseOrderDetailReceiving;
use PurchaseOrderDetailLotReceivingQuery, PurchaseOrderDetailLotReceiving;
use ItemMasterItemQuery, ItemMasterItem;

// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;

use Dplus\Wm\Base;
use Dplus\Wm\Receiving\Strategies\ReadQty\ReadStrategy;
use Dplus\Wm\Receiving\Strategies\EnforcePoItemids\EnforcePoItemids;

/**
 * Items
 * Handles the Reading of the Items from the Purchase Order Detail Lot Received Table
 * Handles Validation of Item Type
 */
class Items extends Base {
	/**
	 * Purchase Order Number
	 * @var string
	 */
	protected $ponbr;

	/** @var ReadStrategy */
	protected $readQtyStrategy;

	/** @var EnforcePoItemids */
	protected $enforceItemidsStrategy;

	/**
	 * Sets Purchase Order Number
	 * @param string $sessionID
	 */
	public function setPonbr($ponbr) {
		$this->ponbr = $ponbr;
	}

	/**
	 * Return Purchase Order Number
	 * @return string
	 */
	public function getPonbr() {
		return $this->ponbr;
	}

	/**
	 * Sets Purchase Order Number
	 * @param ReadStrategy $readQtyStrategy
	 */
	public function setReadQtyStrategy(ReadStrategy $strategy) {
		$this->readQtyStrategy = $strategy;
	}

	/**
	 * Sets Purchase Order Number
	 * @param ReadStrategy $readQtyStrategy
	 */
	public function setEnforceItemidsStrategy(EnforcePoItemids $strategy) {
		$this->enforceItemidsStrategy = $strategy;
	}

/* =============================================================
	Strategy-Based Functions
============================================================= */
	/**
	 * Return Qty Recieved based on Qty Strategy
	 * @param  string $itemID Item ID
	 * @return float
	 */
	public function getQtyReceivedItemid($itemID) {
		if ($this->readQtyStrategy::TYPE == 'single') {
			return $this->countLotSerialsItemid($itemID);
		}
		return $this->sumQtyReceivedItemid($itemID);
	}

	/**
	 * Return if Item ID is allowed to Receive
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function allowItemid($itemID) {
		if ($this->enforceItemidsStrategy->allowItemsNotListed()) {
			return true;
		}
		return $this->isItemOnOrder($itemID);
	}

	/**
	 * Return Qty Received for the Line
	 * @param  int    $linenbr Line Number
	 * @return float
	 */
	public function getQtyRecievedLine(int $linenbr) {
		if ($this->readQtyStrategy::TYPE == 'single') {
			return $this->countLotSerialsLine($linenbr);
		}
		return $this->sumQtyReceivedLine($linenbr);
	}

	/**
	 * Return Qty Received with Lotserial  and Bin
	 * @param  int    $linenbr   Line Number
	 * @param  string $lotserial Lot / Serial #
	 * @param  string $binID     Bin ID
	 * @return float
	 */
	public function getQtyReceivedLineLotserial(int $linenbr, $lotserial, $binID) {
		if ($this->readQtyStrategy::TYPE == 'single') {
			return $this->countLineLotserial($linenbr, $lotserial, $binID);
		}
		return $this->getLineLotserialQty($linenbr, $lotserial, $binID);
	}

/* =============================================================
	ITM Functions
============================================================= */
	/**
	 * Returns if Item is Lot Serial
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function isItemLotserialized($itemID) {
		$v = new MinValidator();
		return $v->itemIsLotSerialized($itemID);
	}

	/**
	 * Returns if Item is Lot Serial
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function isItemSerialized($itemID) {
		$v = new MinValidator();
		return $v->itemIsSerialized($itemID);
	}

	/**
	 * Returns if Item is Lot Serial
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function isItemLotted($itemID) {
		$v = new MinValidator();
		return $v->itemIsLotted($itemID);
	}

	/**
	 * Returns if Item is Lot Serial
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function isItemNormal($itemID) {
		$v = new MinValidator();
		return $v->itemIsNormal($itemID);
	}

/* =============================================================
	Purchaase Order Receiving Items
============================================================= */
	/**
	 * Return if Purchase Order Receiving has Item ID
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function isItemOnOrder($itemID) {
		$q = PurchaseOrderDetailReceivingQuery::create();
		$q->filterByPonbr($this->ponbr);
		$q->filterByItemid($itemID);
		return boolval($q->count());
	}

	/**
	 * Return if PO Receiving Line Exists
	 * @param  int    $linenbr Line Number
	 * @return bool
	 */
	public function lineExists(int $linenbr) {
		$q = PurchaseOrderDetailReceivingQuery::create();
		$q->filterByPonbr($this->ponbr);
		$q->filterByLinenbr($linenbr);
		return boolval($q->count());
	}

	/**
	 * Return the number of records for this PO and Item ID
	 * @param  string $itemID Item ID
	 * @return float
	 */
	public function sumQtyOrderedItemid($itemID) {
		$col = PurchaseOrderDetailReceiving::aliasproperty('qty_ordered');
		$q = PurchaseOrderDetailReceivingQuery::create();
		$q->withColumn("SUM($col)", 'qty');
		$q->select('qty');
		$q->filterByPonbr($this->ponbr);
		$q->filterByItemid($itemID);
		return $q->findOne();
	}

/* =============================================================
	Purchaase Order Receiving Items - Lotserials
============================================================= */
	/**
	 * Return the number of records for this PO and Item ID
	 * @param  string $itemID Item ID
	 * @return int
	 */
	public function countLotSerialsItemid($itemID) {
		$q = PurchaseOrderDetailLotReceivingQuery::create();
		$q->filterByPonbr($this->ponbr);
		$q->filterByItemid($itemID);
		return $q->count();
	}

	/**
	 * Return the number of records for this PO and Item ID
	 * @param  string $itemID Item ID
	 * @return float
	 */
	public function sumQtyReceivedItemid($itemID) {
		$col = PurchaseOrderDetailLotReceiving::aliasproperty('qty_received');
		$q = PurchaseOrderDetailLotReceivingQuery::create();
		$q->withColumn("SUM($col)", 'qty');
		$q->select('qty');
		$q->filterByPonbr($this->ponbr);
		$q->filterByItemid($itemID);
		return $q->findOne();
	}

	/**
	 * Return the number of records for this PO and Item ID
	 * @param  int    $linenbr Line Number
	 * @return int
	 */
	public function countLotSerialsLine(int $linenbr) {
		$q = PurchaseOrderDetailLotReceivingQuery::create();
		$q->filterByPonbr($this->ponbr);
		$q->filterByLinenbr($linenbr);
		return $q->count();
	}

	/**
	 * Return the number of records for this PO and Item ID
	 * @param  int    $linenbr Line Number
	 * @return float
	 */
	public function sumQtyReceivedLine(int $linenbr) {
		$col = PurchaseOrderDetailLotReceiving::aliasproperty('qty_received');
		$q = PurchaseOrderDetailLotReceivingQuery::create();
		$q->withColumn("SUM($col)", 'qty');
		$q->select('qty');
		$q->filterByPonbr($this->ponbr);
		$q->filterByLinenbr($linenbr);
		return $q->findOne();
	}

	/**
	 * Return First Received Lot
	 * @param  string $linenbr   Line Number
	 * @return PurchaseOrderDetailLotReceiving
	 */
	public function getFirstLineLotserial(int $linenbr) {
		$q = PurchaseOrderDetailLotReceivingQuery::create();
		$q->filterByPonbr($this->ponbr);
		$q->filterByLinenbr($linenbr);
		return $q->findOne();
	}

	/**
	 * Return Received Lot from Bin
	 * @param  string $linenbr   Line Number
	 * @param  string $lotserial Lotserial
	 * @param  string $binID     Bin ID
	 * @return PurchaseOrderDetailLotReceiving
	 */
	public function getLineLotserial(int $linenbr, $lotserial, $binID) {
		$q = PurchaseOrderDetailLotReceivingQuery::create();
		$q->filterByPonbr($this->ponbr);
		$q->filterByLinenbr($linenbr);
		$q->filterByLotserial($lotserial);
		$q->filterByBinid($binID);
		return $q->findOne();
	}

	/**
	 * Return Qty Received for Lotserial at Bin
	 * @param  string $linenbr   Line Number
	 * @param  string $lotserial Lotserial
	 * @param  string $binID     Bin ID
	 * @return PurchaseOrderDetailLotReceiving
	 */
	public function getLineLotserialQty(int $linenbr, $lotserial, $binID) {
		$col = PurchaseOrderDetailLotReceiving::aliasproperty('qty_received');
		$q = PurchaseOrderDetailLotReceivingQuery::create();
		$q->withColumn("SUM($col)", 'qty');
		$q->select('qty');
		$q->filterByPonbr($this->ponbr);
		$q->filterByLinenbr($linenbr);
		$q->filterByLotserial($lotserial);
		$q->filterByBinid($binID);
		return $q->findOne();
	}

	/**
	 * Return Received Lot from Bin
	 * @param  string $linenbr   Line Number
	 * @param  string $lotserial Lotserial
	 * @param  string $binID     Bin ID
	 * @return PurchaseOrderDetailLotReceiving
	 */
	public function countLineLotserial(int $linenbr, $lotserial, $binID) {
		$q = PurchaseOrderDetailLotReceivingQuery::create();
		$q->filterByPonbr($this->ponbr);
		$q->filterByLinenbr($linenbr);
		$q->filterByLotserial($lotserial);
		$q->filterByBinid($binID);
		return $q->count();
	}

	/**
	 * Return if Lotserial has is Received
	 * @param  string $linenbr   Line Number
	 * @param  string $lotserial Lotserial
	 * @param  string $binID     Bin ID
	 * @return bool
	 */
	public function lineLotserialExists(int $linenbr, $lotserial, $binID) {
		$q = PurchaseOrderDetailLotReceivingQuery::create();
		$q->filterByPonbr($this->ponbr);
		$q->filterByLinenbr($linenbr);
		$q->filterByLotserial($lotserial);
		$q->filterByBinid($binID);
		return boolval($q->count());
	}
}
