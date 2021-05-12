<?php namespace Dplus\Wm\Receiving;

use Purl\Url;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
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
// Dplus Configs
use Dplus\Configs as Configs;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
use Dplus\CodeValidators\Map as MapValidator;
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

	private $decimalPlacesQty;
	private $decimalPlacesCost;

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

/* =============================================================
	CRUD Functions
============================================================= */
	public function processInput(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'init-receive':
				$this->requestPoInit();
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
			case 'delete-lotserial':
				$this->deleteLotserial($input);
				break;
			case 'update-lotserial-qty':
				$this->updateLotserialQty($input);
				break;
			case 'post-received':
				$this->requestPoPost();
				break;
			case 'create-ilookup':
				$this->createIlookup($input);
				break;
			case 'create-po':
				$this->createPo($input);
				break;
		}
	}

	protected function searchInventory(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;
		$this->requestSearch($values->text('scan'), $values->binID('binID'));
	}

	protected function submitItemReceived(WireInput $input) {
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
		$item->setBin($values->binID('binID'));
		$item->setQty($values->float('qty'));
		$item->setProductiondate($date);
		$saved = $item->save();
		if ($saved === false) {
			return false;
		}
		$received = $this->getSessionLastReceived();
		$received->itemid = $item->itemid;
		$recieved->binid  = $item->bin;

		$this->requestItemReceive();
		return true;
	}

	protected function deleteLotserial(WireInput $input) {
		$rm        = strtolower($input->requestMethod());
		$values    = $input->$rm;
		$ponbr     = $values->text('ponbr');
		$linenbr   = $values->int('linenbr');
		$lotserial = $values->text('lotserial');
		$binID     = $values->binID('binID');

		if ($this->items->lineLotserialExists($linenbr, $lotserial, $binID) === false) {
			return false;
		}
		$lot = $this->items->getLineLotserial($linenbr, $lotserial, $binID);
		$this->requestRemoveLotserial($lot);
		return true;
	}

	protected function updateLotserialQty(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;

		$ponbr      = $values->text('ponbr');
		$linenbr    = $values->int('linenbr');
		$lotserial  = $values->text('lotserial');
		$binID      = $values->binID('originalbinID');
		$newbinID   = $values->binID('binID');
		$date       = $values->text('productiondate');
		$date       = $date ? date('Ymd', strtotime($date)) : 0;

		if ($this->items->lineLotserialExists($linenbr, $lotserial, $binID) === false) {
			return false;
		}
		$lot = $this->items->getLineLotserial($linenbr, $lotserial, $binID);
		$lot->setBinid($newbinID);
		$lot->setLotdate($date);
		$lot->setQty_received($values->float('qty', ['precision' => $this->decimalPlacesQty()]));
		$this->requestUpdateLotserial($lot);
		return true;
	}

	protected function createIlookup(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;

		$ponbr      = $values->text('ponbr');
		$scan       = $values->text('scan');
		$ref        = $values->text('reference');
		$itemID     = $values->text('itemID');
		$validate = new MinValidator();

		if ($validate->itemid($itemID) === false) {
			return false;
		}
		$this->requestCreateIlookup($itemID, $ref);
		return true;
	}

	protected function createPo(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;

		$vendorID = $values->text('vendorID');
		$validate = new MapValidator();

		if ($validate->vendorid($vendorID) === false) {
			return false;
		}

		$this->requestCreatePo($vendorID);
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
		$this->requestItemReceive();
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	/**
	 * Send Request to Start Receiving
	 * @return void
	 */
	public function requestPoInit() {
		$data = array('STARTRECEIVE', "PONBR=$this->ponbr");
		$this->sendDplusRequest($data);
	}

	/**
	 * Send request to Receive Item
	 * @return void
	 */
	public function requestItemReceive() {
		$data = array('ACCEPTRECEIVING', "PONBR=$this->ponbr");
		$this->sendDplusRequest($data);
	}

	/**
	 * Send request to Search Inventory
	 * @return void
	 */
	public function requestSearch($q, $binID) {
		$binID = $this->wire('sanitizer')->binID($binID);
		$data  = array('RECEIVINGSEARCH', "PONBR=$this->ponbr", "QUERY=$q", "BIN=$binID");
		$this->sendDplusRequest($data);
	}

	/**
	 * Send request to Remove Lotserial
	 * @return void
	 */
	public function requestRemoveLotserial(PurchaseOrderDetailLotReceiving $lot) {
		$lot->setBin($this->wire('sanitizer')->binID($lot->bin));
		$data = array('RECEIVEREMOVELOT', "PONBR=$lot->ponbr", "LINENBR=$lot->linenbr", "LOTSERIAL=$lot->lotserial", "BIN=$lot->bin");
		$this->sendDplusRequest($data);
	}

	/**
	 * Send request to Update Received Lotserial
	 * @return void
	 */
	public function requestUpdateLotserial(PurchaseOrderDetailLotReceiving $lot) {
		$oldbin = array_key_exists($lot::aliasproperty('binid'), $lot->originalvalues) ? $lot->originalvalues[$lot::aliasproperty('binid')] : $lot->binid;
		$oldbin = $this->wire('sanitizer')->binID($oldbin);
		$lot->setBin($this->wire('sanitizer')->binID($lot->bin));
		$data = ['EDITRECEIVEDQTY', "PONBR=$lot->ponbr", "LINENBR=$lot->linenbr", "LOTSERIAL=$lot->lotserial", "BIN=$oldbin", "QTY=$lot->qty_received", "DATE=$lot->lotdate"];
		$data[] = "NEWBIN=$lot->bin";
		$this->sendDplusRequest($data);
	}

	/**
	 * Send Request to Start Receiving
	 * @return void
	 */
	public function requestPoPost() {
		$data = array('FINISHRECEIPT', "PONBR=$this->ponbr");
		$this->sendDplusRequest($data);
	}

	/**
	 * Send request to Create Ilookup Reference for Item ID
	 * @return void
	 */
	public function requestCreateIlookup($itemID, $ref) {
		$data = array('RECEIVINGCREATEILOOKUP', "PONBR=$this->ponbr", "ITEMID=$itemID", "REFERENCE=$ref");
		$this->sendDplusRequest($data);
	}

	/**
	 * Send request to Create PO for Vendor
	 * @return void
	 */
	public function requestCreatePo($vendorID) {
		$data = array('CREATERECEIVEPO', "VENDORID=$vendorID");
		$this->sendDplusRequest($data);
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Returns Purchase Order from Database
	 * @return PurchaseOrder
	 */
	public function getPurchaseorder() {
		return PurchaseOrderQuery::create()->findOneByPonbr($this->ponbr);
	}

	/**
	 * Return the number of decimal places for qty values
	 * @return int
	 */
	public function decimalPlacesQty() {
		if (empty($this->decimalPlacesQty)) {
			$config = Configs\So::config();
			$this->decimalPlacesQty = $config->decimal_places;
		}
		return $this->decimalPlacesQty;
	}

	/**
	 * Return the number of decimal places for Cost values
	 * @return int
	 */
	public function decimalPlacesCost() {
		if (empty($this->decimalPlacesCost)) {
			$config = Configs\Po::config();
			$this->decimalPlacesCost = $config->decimal_places_cost;
		}
		return $this->decimalPlacesCost;
	}

	public function canAutoSubmit(Whseitemphysicalcount $item) {
		$validate = new MinValidator();

		if ($validate->itemid($item->itemid) === false) {
			return false;
		}

		if ($validate->whsebin($this->wire('user')->whseid, $this->wire('sanitizer')->binID($item->bin)) === false) {
			return false;
		}

		if (empty($item->productiondate) || empty($item->lotserialref) || empty($item->qty)) {
			return false;
		}

		return true;
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

	public function getReadQtyStrategy() {
		$config = $this->wire('modules')->get('ConfigsWarehouseInventory');
		return $config->receive_lotserial_as_single ? new Strategies\ReadQty\LotserialSingle() : new Strategies\ReadQty\LotserialQty();
	}

	public function getEnforceQtyStrategy() {
		$config = $this->wire('config');
		return $config->company == 'ugm' ? new Strategies\EnforceQty\Relaxed() : new Strategies\EnforceQty\Warn();
	}

	public function getEnforceItemidsStrategy() {
		$config = $this->wire('config');
		return $config->company == 'ugm' ? new Strategies\EnforcePoItemids\Relaxed() : new Strategies\EnforcePoItemids\Enforced();
	}

	public function getCreatePoStrategy() {
		$config = $this->wire('modules')->get('ConfigsWarehouseInventory');
		return $config->receive_create_po === true ? new Strategies\CreatePo\Allow() : new Strategies\CreatePo\Forbid();
	}

	public function init() {
		$this->strategies = new WireData();
		$this->strategies->readQty        = $this->getReadQtyStrategy();
		$this->strategies->enforceQty     = $this->getEnforceQtyStrategy();
		$this->strategies->enforceItemids = $this->getEnforceItemidsStrategy();
		$this->strategies->createPo       = $this->getCreatePoStrategy();
		$this->items->setReadQtyStrategy($this->strategies->readQty);
		$this->items->setEnforceItemidsStrategy($this->strategies->enforceItemids);
	}
}
