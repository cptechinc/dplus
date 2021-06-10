<?php namespace Dplus\Wm\Sop\Picking;
// Dpluso Model
use PickSalesOrderDetailQuery, PickSalesOrderDetail;
use WhseitempickQuery, Whseitempick;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\WireInputData;
// Dplus Configs
use Dplus\Configs as Configs;
// Dplus Wm
use Dplus\Wm\Base;
use Dplus\Wm\Sop\Picking\Items;
use Dplus\Wm\Sop\Picking\Strategies\PackBin as PackBinStrategies;
use Dplus\Wm\Sop\Picking\Strategies\PackBin\PackBin as PackBinStrategy;

/**
 * Picking
 *
 * Handles the picking functionality
 * 1. Retrieves Order Items
 * 2. Adds items to be picked for order in a working file
 * 3. Validates Items against inventory
 *
 * @property string                   $sessionID Session ID
 * @property string                   $ordn      Order Number
 * @property Items                    $items     Handles Items
 * @property Inventory                $inventory
 * @property ConfigsWarehousePicking
 * @property ConfigsWarehouseInventory
 */
class Picking extends Base {
	/** @var string Sales Order Number */
	protected $ordn;
	/** @var Items */
	public $items;
	/** @var Inventory */
	public $inventory;
	/** @var ConfigsWarehousePicking */
	private $configPicking;
	/** @var ConfigsWarehouseInventory */
	private $configInventory;
	/** @var int */
	private $decimalPlacesQty;
	/** @var PackBinStrategy */
	private $packBinStrategy;

	public function __construct() {
		$this->items = new Items();
		$this->inventory = new Inventory();
		$this->setSessionID(session_id());
	}

	/**
	 * Sets Sales Order Number
	 * @param string $sessionID
	 */
	public function setOrdn($ordn) {
		$this->ordn = $ordn;
		$this->items->setOrdn($ordn);
	}

	/**
	 * Return Sales Order Number
	 * @return string
	 */
	public function getOrdn() {
		return $this->ordn;
	}

	/**
	 * Sets Session ID
	 * @param string $sessionID
	 */
	public function setSessionID($sessionID = '') {
		parent::setSessionID($sessionID);
		$this->items->setSessionID($this->sessionID);
		$this->inventory->setSessionID($this->sessionID);
	}

	/**
	 * Return Picking Config
	 * @return ConfigsWarehousePicking
	 */
	public function getConfigPicking() {
		if (empty($this->configPicking)) {
			$this->configPicking = $this->wire('modules')->get('ConfigsWarehousePicking');
		}
		return $this->configPicking;
	}

	/**
	 * Return Inventory Config
	 * @return ConfigsWarehouseInventory
	 */
	public function getConfigInventory() {
		if (empty($this->configInventory)) {
			$this->configInventory = $this->wire('modules')->get('ConfigsWarehouseInventory');
		}
		return $this->configInventory;
	}

	/**
	 * Return Inventory Config
	 * @return Configs\So
	 */
	public function getConfigSo() {
		return Configs\So::config();
	}

	/**
	 * Return If Picking More than Ordered is allowed
	 * @return bool
	 */
	public function allowOverPick() {
		return $this->getConfigSo()->allow_overpick == 'Y';
	}


	/**
	 * Return Inventory Config
	 * @return ConfigsWarehouseInventory
	 */
	public function getDecimalPlacesQty() {
		if (empty($this->decimalPlacesQty)) {
			$config = Configs\So::config();
			$this->decimalPlacesQty = $config->decimal_places;
		}
		return $this->decimalPlacesQty;
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
	/**
	 * Takes the Wire Input and decides which action to take based on the
	 * action given
	 * @param  WireInput $input Values to pull from
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->action) {
			switch ($values->text('action')) {
				case 'start-order':
					$this->requestStartOrder($values->text('ordn'));
					break;
				case 'exit-order':
					$this->requestExitOrder($values->text('ordn'));
					break;
				case 'scan-pick-item':
					$this->inventory->requestSearch($values->text('ordn'), $values->text('scan'));
					break;
				case 'add-lotserial':
					$this->addLotserial($input);
					break;
				case 'add-lotserials':
					$this->addLotserials($input);
					break;
				case 'delete-lotserial':
					$this->deleteLotserial($input);
					break;
				// case 'verify-whseitempicks':
				// 	$this->verifyWhseitempicks($input);
				// 	break;
				// case 'finish-order':
				// 	$this->requestFinishOrder($values->text('ordn'));
				// 	break;
				// case 'unlock-order':
				// 	$this->requestUnlockOrder($values->text('ordn'));
				// 	break;
			}
		}
	}

	/**
	 * Adds Whseitempick record for one lotserial
	 * @param WireInput $input
	 */
	public function addLotserial(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID    = $values->text('itemID');
		$lotserial = $values->text('lotserial');
		$binID     = $values->text('binID');
		$scan      = $values->text('scan');
		$orderitem = $this->items->getItemByItemid($itemID);

		if ($this->doesWhseitempickExist($orderitem, $lotserial, $binID)) {
			$added = boolval($this->addLotserialAlreadyPicked($orderitem, $values));
			if ($added) {
				$this->requestLineUpdate($orderitem->linenbr);
			}
			return $added;
		}
		$pickingitem = $this->createWhseitempickInput($orderitem, $input);
		$pickingitem->save();
		return true;
	}

	/**
	 * Add Previously Picked Lotserial
	 * @param PickSalesOrderDetail $orderitem Order Line Item
	 * @param WireInputData        $values
	 */
	private function addLotserialAlreadyPicked(PickSalesOrderDetail $orderitem, WireInputData $values) {
		$itemID    = $values->text('itemID');
		$lotserial = $values->text('lotserial');
		$binID     = $values->text('binID');
		$scan      = $values->text('scan');

		$filters = ['linenbr' => $orderitem->linenbr, 'sublinenbr' => $orderitem->sublinenbr, 'lotserial' => $lotserial, 'bin' => $binID];
		$q = $this->getWhseitempickQuery($filters);

		$existsForOrderItem = boolval($q->count());

		if ($existsForOrderItem) {
			$qty         = $values->float('qty');
			$pickingitem = $q->findOne();
			$pickingitem->setQty($pickingitem->qty + $qty);
			$pickingitem->save();
			return $pickingitem->recordnumber;
		}
		self::pw('session')->setFor('picking', 'error', "$scan has been added to another line already");
		return false;
	}

	/**
	 * Delete Lotserial that has been picked for a line
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function deleteLotserial(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$recordnumber = $values->int('recordnumber');
		$linenbr      = $values->int('linenbr');
		$sublinenbr   = $values->int('sublinenbr');
		$q = $this->getWhseitempickQuery(['recordnumber' => $recordnumber, 'linenbr' => $linenbr, 'sublinenbr' => $sublinenbr]);
		if ($q->count() == 0) {
			return false;
		}
		$this->requestDeletePicked($recordnumber);
		$this->wire('session')->setFor('picking', 'removed-from-line', $linenbr);
		return true;
	}

	/**
	 * Adds Whseitempick records for one lotserial
	 * @param WireInput $input
	 */
	public function addLotserials(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID    = $values->text('itemID');
		$lotserialbins = $values->array('lotserialbins');
		$orderitem     = $this->items->getItemByItemid($itemID);
		$recordnumbers = [];


		foreach ($lotserialbins as $lotserialbin) {
			echo $lotserialbin;
			echo '<br /> <br />';
			$data = new WireData();
			$data->qty       = $values->text("$lotserialbin-qty");
			$data->binID     = $values->text("$lotserialbin-bin");
			$data->lotref    = $values->text("$lotserialbin-lotserialref");
			$data->lotserial = $values->text("$lotserialbin-lotserial");

			$recnbr = $this->addLotserialsSingle($orderitem, $input, $data);

			if ($recnbr) {
				$recordnumbers[] = $recnbr;
			}
		}

		if ($this->getConfigPicking()->verify_whseitempicks) {
			$session = $this->wire('session');
			$session->setFor('picking', 'verify-picked-itemid', $itemID);
			$session->setFor('picking', 'verify-picked-linenbr', $orderitem->linenbr);
			$session->setFor('picking', 'verify-picked-items', $recordnumbers);
			return true;
		}
		$this->requestLineUpdate($orderitem->linenbr);
		return true;
	}

	private function addLotserialsSingle(PickSalesOrderDetail $orderitem, WireInput $input, WireData $data) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		// CHECKS if ITEM EXISTS ON ORDER
		$hasBeenPicked = $this->doesWhseitempickExist($orderitem, $data->lotserial, $data->binID);

		if ($hasBeenPicked) {
			$values->qty       = $data->qty;
			$values->lotserial = $data->lotserial;
			$values->binID     = $data->binID;
			$recnbr = $this->addLotserialAlreadyPicked($orderitem, $values);
			return $recnbr;
		}

		if ($data->qty + 0 > 0) {
			$values->qty          = $data->qty;
			$values->lotserial    = $data->lotserial;
			$values->lotserialref = $data->lotref;
			$values->binID        = $data->binID;

			$pickingitem = $this->createWhseitempickInput($orderitem, $input);
			$pickingitem->save();
			return $pickingitem->recordnumber;
		}
		return false;
	}

/* =============================================================
	Whseitempick Functions
============================================================= */
	/**
	 * Return WhseitempickQuery
	 * @param  array  $filters Column or aliases to filter on ex. array('linenbr' => 2)
	 * @return WhseitempickQuery
	 */
	public function getWhseitempickQuery($filters = []) {
		$q = WhseitempickQuery::create();
		$q->filterBySessionid($this->sessionID);
		$q->filterByOrdn($this->ordn);

		foreach ($filters as $filter => $value) {
			$function = 'filterBy' . ucfirst($filter);
			$q->$function($value);
		}
		return $q;
	}

	/**
	 * Returns if we have picked this item already for this order
	 * @param  PickSalesOrderDetail $orderitem Order Item to pick
	 * @param  string               $lotserial Lot / Serial Number
	 * @return bool
	 */
	public function doesWhseitempickExist(PickSalesOrderDetail $orderitem, $lotserial, $binID = '') {
		$q = $this->getWhseitempickQuery();
		$q->filterByItemid($orderitem->itemnbr);
		$q->filterByLotserial($lotserial);

		if ($binID) {
			$q->filterByBin($binID);
		}
		return boolval($q->count());
	}

	/**
	 * Return Pick Item Sum
	 * @param  PickSalesOrderDetail $orderitem
	 * @return float
	 */
	protected function getItemPickedLotserials(PickSalesOrderDetail $orderitem) {
		$q = $this->getWhseitempickQuery();
		$q->withColumn('SUM(qty)', 'qty');
		$q->select(array('qty', 'bin', 'lotserial'));
		$q->filterBySessionid($orderitem->sessionID);
		$q->filterByOrdn($orderitem->ordernbr);
		$q->filterByLinenbr($orderitem->linenbr);
		$q->filterBySublinenbr($orderitem->sublinenbr);
		$q->groupBy(['bin', 'lotserial']);
		return $q->find();
	}

	/**
	 * Return Whseitempick with values set from the Sales Order Item and Input
	 * @param  PickSalesOrderDetail $orderitem Order Item to pick
	 * @param  WireInput            $input
	 * @return Whseitempick
	 */
	private function createWhseitempickInput(PickSalesOrderDetail $orderitem, WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$q = $this->getWhseitempickQuery();

		$item = new Whseitempick();
		$item->setSessionid($orderitem->sessionid);
		$item->setOrdn($orderitem->ordernbr);
		$item->setItemid($orderitem->itemnbr);
		$item->setRecordnumber($q->get_max_order_subline_recordnumber($orderitem->sessionid, $orderitem->ordernbr, $orderitem->linenbr, $orderitem->sublinenbr) + 1);
		$item->setPalletnbr($values->palletnbr ? $values->text('palletnbr') : 1);
		$item->setLinenbr($orderitem->linenbr);
		$item->setSublinenbr($orderitem->sublinenbr);
		$item->setBarcode($values->text('scan'));
		$item->setBin($values->binID('binID'));
		$item->setLotserialref($values->text('lotserialref'));
		$item->setLotserial($values->text('lotserial'));

		$qty = $this->inventory->isItemSerialized($orderitem->itemnbr) ? 1 : $values->float('qty');
		$item->setQty($qty);
		echo var_dump($item);
		return $item;
	}

/* =============================================================
	Dplus Request Functions
============================================================= */
	/**
	 * Send Request to Start & Load Order
	 * @param  string $ordn   Order Number
	 * @return bool
	 */
	public function requestStartOrder($ordn) {
		$data = ['STARTORDER', "ORDERNBR=$ordn"];
		$this->sendDplusRequest($data);
	}

	/**
	 * Send Request to Start & Load Order
	 * @param  string $ordn   Order Number
	 * @return bool
	 */
	public function requestExitOrder($ordn) {
		$data = ['STOPORDER', "ORDERNBR=$ordn"];
		$this->sendDplusRequest($data);
	}

	/**
	 * Send Request to Start Picking Unguided
	 * @param  string $ordn   Order Number
	 * @return bool
	 */
	public function requestStartPicking() {
		$data = ['PICKUNGUIDED'];
		$this->sendDplusRequest($data);
	}

	/**
	 * Send Request to Save Picked Line Items
	 * @param  int   $linenbr Line Number
	 * @param  bool  $debug   Is this for Debug
	 * @return bool
	 */
	public function requestLineUpdate(int $linenbr, $debug = false) {
		$orderitem = $this->items->getItemByLinenbr($linenbr);
		$data = ['ACCEPTITEM', "ORDERNBR=$this->ordn", "LINENBR=$linenbr", "ITEMID=$orderitem->itemnbr"];
		$data = $this->writeAcceptitemData($data, $orderitem);
		$this->sendDplusRequest($data);
	}

	/**
	 * Appends Picked Line Item data to array
	 * @param  array                $data      Dplus Request array
	 * @param  PickSalesOrderDetail $orderitem Sales Order Item
	 * @return array
	 */
	public function writeAcceptitemData(array $data, PickSalesOrderDetail $orderitem) {
		$barcodes = $this->getItemPickedLotserials($orderitem);

		foreach ($barcodes as $barcode) {
			$qty       = $barcode['qty'];
			$binID     = $this->wire('sanitizer')->binID($barcode['bin']);
			$lotserial = $barcode['lotserial'];
			$data[]    = "BIN=$binID|LOTSERIAL=$lotserial|QTY=$qty";
		}
		return $data;
	}

	/**
	 * Send Request to Save Picked Line Items
	 * @param  int   $recnbr  Record Number
	 * @param  bool  $debug   Is this for Debug
	 * @return bool
	 */
	public function requestDeletePicked(int $recnbr, $debug = false) {
		$whseitempick = $this->getWhseitempickQuery($filters = ['recordnumber' => $recnbr])->findOne();
		$whseitempick->setBin(strtoupper($whseitempick->bin));

		$data = ['PICKITEMREMOVE', "ORDERNBR=$this->ordn", "LINENBR=$whseitempick->linenbr", "ITEMID=$whseitempick->itemid"];
		$data[] = "BIN=$whseitempick->bin|LOTSERIAL=$whseitempick->lotserial|QTY=$whseitempick->qty";
		$this->sendDplusRequest($data);
	}

/* =============================================================
	Init Functions
============================================================= */
	public function init() {

	}
}
