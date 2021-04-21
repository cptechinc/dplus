<?php namespace Dplus\Wm\Receiving;

use Purl\Url;
// ProcessWire
use ProcessWire\WireData;
// Dplus Online Models
use PurchaseOrderQuery, PurchaseOrder;
use PurchaseOrderDetailReceivingQuery, PurchaseOrderDetailReceiving;
use PurchaseOrderDetailLotReceivingQuery, PurchaseOrderDetailLotReceiving;
use WarehouseQuery, Warehouse;
use ConfigSalesOrderQuery, ConfigSalesOrder;
use ItemMasterItemQuery, ItemMasterItem;
// Dplus Online Models
use WhsesessionQuery, Whsesession;
use WhseitemphysicalcountQuery, Whseitemphysicalcount;

// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Dplus Wm
use Dplus\Wm\Base;
use Dplus\Wm\Receiving\Strategies as Strategies;

class Receiving extends Base {
	/**
	 * Purchase Order Number
	 * @var string
	 */
	protected $ponbr;

	/** @var Items */
	public $items;

	/**
	 * Sets Purchase Order Number
	 * @param string $sessionID
	 */
	public function setPonbr($ponbr) {
		$this->ponbr = $ponbr;
		$this->items->setPonbr($ponbr);
	}

	/**
	 * Return Purchase Order Number
	 * @return string
	 */
	public function getPonbr() {
		return $this->ponbr;
	}

	public function __construct() {
		$this->items = new Items();
	}

	public function processInput(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'init-receive':
				$m->requestPoInit();
				break;
			case 'search-inventory':
				$this->searchInventory($input);
				break;
			case 'autosubmit-scan':
				$this->requestPoAutoSubmit();
				break;
			case 'submit-item':
				$this->submitItemReceived($input);
				break;
		}
	}

	protected function searchInventory(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;
		$m->requestSearch($values->text('scan'), $values->text('binID'));
	}

	public function submitItemReceived(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;
		$scan   = $values->text('scan');
		$ponbr  = $values->text('ponbr');
		$date   = $values->text('productiondate');
		$date   = $date ? date('Ymd', strtotime($date)) : 0;
		$q = $this->getInventoryQuery($values->text('scan'));
		$item = $q->findOne();

		$item->setItemid($values->text('itemID'));
		$item->setLotserial($values->text('lotserial'));
		$item->setLotserialref($values->text('lotserialref'));
		$item->setBin($values->text('binID'));
		$item->setQty($values->float('qty'));
		$item->setProductiondate($date);
		$saved = $item->save();
		if ($saved === false) {
			return false;
		}
		$received = $this->getSessionLastReceived();
		$received->itemid = $item->itemid;
		$recieved->binid  = $item->bin;

		$this->requestAutoSubmitScan();
		return true;
	}

	public function canAutoSubmit(Whseitemphysicalcount $item) {
		$validate = new MinValidator();

		if ($validate->itemid($item->itemid) === false) {
			return false;
		}

		$warehouse = $this->getWarehouse();

		if (!$warehouse->validate_bin($item->bin)) {
			return false;
		}

		if (empty($item->productiondate) || empty($item->lotserialref) || empty($item->qty)) {
			return false;
		}

		return true;
	}

	public function autoSubmitScan($scan) {
		$q = WhseitemphysicalcountQuery::create();
		$q->filterBySessionid($this->sessionID);
		$q->filterByScan($scan);

		if ($q->count() != 1) {
			return false;
		}

		$item = $q->findOne();
		$received = $this->getSessionLastReceived();
		$received->itemid = $item->itemid;
		$received->binid  = $item->bin;
		$this->requestAutoSubmitScan();
	}

	public function getInventoryQuery($scan = '') {
		$q = WhseitemphysicalcountQuery::create();
		$q->filterBySessionid($this->sessionID);
		if ($scan) {
			$q->filterByScan($scan);
		}
		return $q;
	}

	public function getSessionLastReceived() {
		if (empty($this->wire('session')->getFor('receiving', 'received'))) {
			$received = new WireData();
			$this->wire('session')->setFor('receiving', 'received', $received);
		}
		return $this->wire('session')->getFor('receiving', 'received');
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	/**
	 * Sends HTTP GET request to Redir to make Dplus Request to load Purchase Order Working files
	 * @return void
	 */
	public function requestPoInit() {
		$data = array('STARTRECEIVE', "PONBR=$this->ponbr");
		$this->sendDplusRequest($data);
	}

	/**
	 * Sends HTTP GET request to Redir to make Dplus Request to load Purchase Order Working files
	 * @return void
	 */
	public function requestAutoSubmitScan() {
		$data = array('ACCEPTRECEIVING', "PONBR=$this->ponbr");
		$this->sendDplusRequest($data);
	}

	/**
	 * Sends HTTP GET request to Redir to make Dplus Request to load Purchase Order Working files
	 * @return void
	 */
	public function requestSearch($q, $binID) {
		$data = array('RECEIVINGSEARCH', "PONBR=$this->ponbr", "QUERY=$q", "BIN=$binID");
		$this->sendDplusRequest($data);
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Returns if Purchase Order exists in the Database
	 * @return PurchaseOrder
	 */
	public function purchaseorder_exists() {
		return PurchaseOrderQuery::create()->filterByPonbr($this->ponbr)->count();
	}

	/**
	 * Returns Purchase Order from Database
	 * @return PurchaseOrder
	 */
	public function getPurchaseorder() {
		return PurchaseOrderQuery::create()->findOneByPonbr($this->ponbr);
	}

	/**
	 * Returns Purchase Order from Database
	 * @return Whsesession
	 */
	public function getWhsession() {
		return WhsesessionQuery::create()->findOneBySessionid($this->sessionID);
	}

	/**
	 * Return Warehouse
	 * @return Warehouse
	 */
	public function getWarehouse() {
		$whsesession = $this->getWhsession();
		return WarehouseQuery::create()->findOneById($whsesession->whseid);
	}

	/**
	 * Return the number of decimal places for qty values
	 * @return int
	 */
	public function decimal_places() {
		return ConfigSalesOrderQuery::create()->findOne()->decimal_places;
	}

	/**
	 * Return the Sum of Qty Received for Item ID
	 * @param  string $itemID Item ID
	 * @return float
	 */
	public function get_received_total($itemID) {
		$col_received = PurchaseOrderDetailReceiving::get_aliasproperty('qty_received');
		$q = PurchaseOrderDetailReceivingQuery::create();
		$q->withColumn("SUM($col_received)", 'total');
		$q->select("total");
		$q->filterByPonbr($this->ponbr);
		$q->filterByItemid($itemID);
		return $q->findOne();
	}

	/**
	 * Return Receiving Item
	 * @param  int    $linenbr      Line Number
	 * @param  string $lotserial Lot / Serial Number
	 * @param  string $binID     Bin ID
	 * @return PurchaseOrderDetailLotReceiving
	 */
	public function get_receiving_item($linenbr, $lotserial = '', $binID = null) {
		$q = PurchaseOrderDetailLotReceivingQuery::create();
		$q->filterByPonbr($this->ponbr);
		$q->filterByLinenbr($linenbr);

		if ($lotserial)  {
			if ($lotserial != 'all') {
				$q->filterByLotserial($lotserial);
			}
		}

		if ($binID) {
			if ($lotserial != 'all') {
				$q->filterByBin($binID);
			}
		}
		return $q->findOne();
	}

	/**
	 * Returns an Array that contains each Purchase Order Detail and the properties needed
	 * for JavaScript Validation
	 * @return array
	 */
	public function get_purchaseorder_recevingdetails_js() {
		$config = $this->wire('config');
		$po = $this->getPurchaseorder();
		$items = $po->get_receivingitems();
		$js = array();

		foreach ($items as $item) {
			$js[$item->itemid] = array(
				'itemid'         => $item->itemid,
				'qty_ordered'    => intval($item->qty_ordered),
				'qty_received'   => $config->company == 'provalley' ? $item->count_receivedlots() : $item->qty_received,
				'lotserialcount' => $item->count_receivedlots()
			);
		}
		return $js;
	}

	/**
	 * Returns array that contains configuration values
	 * NOTE: used for JavaScript
	 * @return array
	 */
	public function get_jsconfig() {
		$config = $this->wire('modules')->get('ConfigsWarehouseInventory');
		$js = array(
			'receive_lotserial_as_single' => $config->receive_lotserial_as_single,
			//'receive_qty_label'           => $config->receive_qty_label
		);
		return $js;
	}

	/**
	 * Returns if Item is Lot Serial
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function is_item_lotserialized($itemID) {
		$q = ItemMasterItemQuery::create();
		return ($q->is_item_serialized($itemID) || $q->is_item_lotted($itemID));
	}

	/**
	 * Returns if Item is Lot Serial
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function is_item_serialized($itemID) {
		$q = ItemMasterItemQuery::create();
		return $q->is_item_serialized($itemID);
	}

	/**
	 * Returns if Item is Lot Serial
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function is_item_lotted($itemID) {
		$q = ItemMasterItemQuery::create();
		return $q->is_item_lotted($itemID);
	}

	/**
	 * Returns if Item is Lot Serial
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function is_item_normal($itemID) {
		$q = ItemMasterItemQuery::create();
		return $q->is_item_normal($itemID);
	}

	public function getReadQtyStrategy() {
		$config = $this->wire('modules')->get('ConfigsWarehouseInventory');
		return $config->receive_lotserial_as_single ? new Strategies\ReadQty\LotserialSingle() : new Strategies\ReadQty\LotserialQty();
	}

	public function getEnforiceQtyStrategy() {
		$config = self::pw('config');
		return $config->company == 'ugm' ? new Strategies\EnforceQty\Relaxed() : new Strategies\EnforceQty\Warn();
	}

/* =============================================================
	ProcessWire Module Functions
============================================================= */

	public function init() {
		$this->strategies = new WireData();
		$this->strategies->readQty    = $this->getReadQtyStrategy();
		$this->strategies->enforceQty = $this->getEnforceQtyStrategy();
		$this->items->setReadQtyStrategy($this->strategies->readQty);
	}
}
