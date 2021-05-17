<?php namespace Dplus\Wm\Sop\Picking;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;

use Dplus\Wm\Base;
use Dplus\Wm\Sop\Picking\Items;


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
				// case 'add-lotserials':
				// 	$this->addLotserials($input);
				// 	break;
				// case 'add-lotserial':
				// 	$this->addLotserial($input);
				// 	break;
				// case 'delete-lotserial':
				// 	$this->deleteLotserial($input);
				// 	break;
				// case 'verify-whseitempicks':
				// 	$this->verifyWhseitempicks($input);
				// 	break;
				// case 'scan-pick-item':
				// 	$this->inventory->sendScanRequest($values->text('ordn'), $values->text('scan'));
				// 	break;
				// case 'exit-order':
				// 	$this->requestExitOrder($values->text('ordn'));
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
	 * Send Request to Start Picking Unguided
	 * @param  string $ordn   Order Number
	 * @return bool
	 */
	public function requestStartPicking() {
		$data = ['PICKUNGUIDED'];
		$this->sendDplusRequest($data);
	}

/* =============================================================
	Init Functions
============================================================= */
	public function init() {

	}
}
