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
		}
	}

	protected function searchInventory(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;
		$this->requestSearch($values->text('scan'), $values->text('binID'));
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

	protected function deleteLotserial(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;
		$ponbr     = $values->text('ponbr');
		$linenbr   = $values->int('linenbr');
		$lotserial = $values->text('lotserial');
		$binID     = $values->text('binID');

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

		$ponbr     = $values->text('ponbr');
		$linenbr   = $values->int('linenbr');
		$lotserial = $values->text('lotserial');
		$binID     = $values->text('binID');

		if ($this->items->lineLotserialExists($linenbr, $lotserial, $binID) === false) {
			return false;
		}
		$lot = $this->items->getLineLotserial($linenbr, $lotserial, $binID);
		$lot->setLotdate($values->text('productiondate'));
		$lot->setQty_received($values->text('qty'));
		$this->requestUpdateLotserialQty($lot);
		return true;
	}

	public function canAutoSubmit(Whseitemphysicalcount $item) {
		$validate = new MinValidator();

		if ($validate->itemid($item->itemid) === false) {
			return false;
		}

		if ($validate->whsebin($this->wire('user')->whseid, $item->bin) === false) {
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

	/**
	 * Sends HTTP GET request to Redir to make Dplus Request to load Purchase Order Working files
	 * @return void
	 */
	public function requestRemoveLotserial(PurchaseOrderDetailLotReceiving $lot) {
		$data = array('RECEIVEREMOVELOT', "PONBR=$lot->ponbr", "LINENBR=$lot->linenbr", "LOTSERIAL=$lot->lotserial", "BIN=$lot->bin");
		$this->sendDplusRequest($data);
	}

	/**
	 * Sends HTTP GET request to Redir to make Dplus Request to load Purchase Order Working files
	 * @return void
	 */
	public function requestUpdateLotserialQty(PurchaseOrderDetailLotReceiving $lot) {
		$data = ['EDITRECEIVEDQTY', "PONBR=$lot->ponbr", "LINENBR=$lot->linenbr", "LOTSERIAL=$lot->lotserial", "BIN=$lot->bin", "QTY=$lot->qty_received", "DATE=$lot->lotdate"];
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
	public function decimal_places() {
		return ConfigSalesOrderQuery::create()->findOne()->decimal_places;
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

/* =============================================================
	ProcessWire Module Functions
============================================================= */

	public function init() {
		$this->strategies = new WireData();
		$this->strategies->readQty        = $this->getReadQtyStrategy();
		$this->strategies->enforceQty     = $this->getEnforceQtyStrategy();
		$this->strategies->enforceItemids = $this->getEnforceItemidsStrategy();
		$this->items->setReadQtyStrategy($this->strategies->readQty);
		$this->items->setEnforceItemidsStrategy($this->strategies->enforceItemids);
	}
}
