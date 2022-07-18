<?php namespace Dplus\Cart;
// Dplus Databases
use Dplus\Databases\Connectors\Dpluso as DbDpluso;
// Dplus Models
use CustomerQuery, Customer;
use CustomerShiptoQuery, CustomerShipto;
// Dpluso Models
use CartdetQuery, Cartdet;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Configs
use Dplus\Configs;

class Cart extends WireData {
	private static $instance;
	private $sessionID;
	private $header;
	private $items;

/* =============================================================
	Constructors
============================================================= */
	public function __construct() {
		$this->sessionID = session_id();
		$this->header = new Header();
		$this->items  = new Items();
	}

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Setters
============================================================= */
	/**
	 * Set Session ID
	 * @param string $sessionID
	 */
	public function setSessionid($sessionID) {
		$this->sessionID = $sessionID;
		$this->header->setSessionid($sessionID);
		$this->items->setSessionid($sessionID);
	}

/* =============================================================
	Property Functions
============================================================= */
	/**
	 * Return if Cust ID has been set
	 * @return bool
	 */
	public function hasCustid() {
		return $this->header->hasCustid();
	}

	/**
	 * Return Cust ID
	 * @return string
	 */
	public function getCustid() {
		return $this->header->getCustid();
	}

	/**
	 * Return if Ship-to ID has been set
	 * @return bool
	 */
	public function hasShiptoid() {
		return $this->header->hasShiptoid();
	}

	/**
	 * Return Cust ID
	 * @return string
	 */
	public function getShiptoid() {
		return $this->header->getShiptoid();
	}

	/**
	 * Return Items
	 * @return Cartdet[]|ObjectCollection
	 */
	public function getItems() {
		return $this->items->getItems();
	}

	/**
	 * Set Customer and Shipto for Cart
	 * @param string $custID    Customer ID
	 * @param string $shiptoID  Customer Shipto ID
	 */
	public function setCustid($custID, $shiptoID = '') {
		$this->header->setCustid($custID);

		if ($shiptoID) {
			$this->header->setShiptoid($shiptoID);
		}
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
	/**
	 * Process Input Data and Execute action
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'set-customer':
				$this->inputUpdateCustomer($input);
				break;
			case 'set-shiptoid':
				$this->inputUpdateShipto($input);
				break;
			case 'add-item':
				$this->inputAddItem($input);
				break;
			case 'add-popular-items':
				$this->inputAddMultipleItems($input);
				break;
			case 'update-item-quick':
				$this->inputUpdateItemQuick($input);
				break;
			case 'delete-item':
				$this->inputDeleteItem($input);
				break;
			case 'empty-cart':
				$this->requestEmptyCart();
				break;
			case 'create-order':
				$this->requestCreateOrder();
				break;
			case 'create-quote':
				$this->requestCreateQuote();
				break;
			case 'create-blank-order':
				if ($this->inputUpdateCustomer($input)) {
					$this->requestCreateOrder();
				}
				break;
		}
	}

	/**
	 * Set Customer
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function inputUpdateCustomer(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$custID = $values->text('custID');
		$isSet  = $this->header->setCustid($custID);

		if ($isSet === false) {
			return false;
		}
		if ($values->offsetExists('shiptoID') === false) {
			return $isSet;
		}
		return $this->inputUpdateShipto($input);
	}

	/**
	 * Update Shipto ID
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function inputUpdateShipto(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$shiptoID = $values->text('shiptoID');

		if ($values->offsetExists('shiptoID')) {
			$this->header->setShiptoid($values->text('shiptoID'));
			return true;
		}

		$q = CustomerShiptoQuery::create()->filterByCustid($values->text('custID'));

		if ($q->count() == 1) {
			$shipto = $q->findOne();
			$this->header->setShiptoid($shipto->shiptoid);
			return true;
		}

		$q->filterByPrimary(CustomerShipto::YN_TRUE);
		if ($q->count()) {
			$shipto = $q->findOne();
			 return $this->header->setShiptoid($shipto->shiptoid);
		}
		return true;
	}

	/**
	 * Add Item
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function inputAddItem(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->text('itemID');
		$qty    = $values->float('qty');
		$price  = $values->text('price');
		$this->requestAddItem($itemID, $qty, $price);
		return true;
	}

	/**
	 * Add Multiple Items
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function inputAddMultipleItems(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$qty    = $values->array('qty');
		$itemID = $values->array('itemID');
		$this->requestAddMultipleItems($itemID, $qty);
		return true;
	}

	/**
	 * Update Item
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function inputUpdateItemQuick(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values  = $input->$rm;
		$linenbr = $values->int('linenbr');
		$item    = $this->items->getItemByLine($linenbr);
		if (empty($item)) {
			return false;
		}
		$this->updateItemQuick($item, $input);
		$saved = $item->save();

		if ($saved) {
			$this->requestUpdateItem($linenbr);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Update Cart Detail from Input Data
	 * @param  Cartdet   $item  Line Item
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	private function updateItemQuick(Cartdet $item, WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$item->setQty($values->float('qty'));
		$item->setPrice($values->float('price'));
		$item->setRshipdate($values->text('rqstdate'));
	}

	/**
	 * Delete Item using Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function inputDeleteItem(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values  = $input->$rm;
		$linenbr = $values->int('linenbr');
		$item    = $this->items->getItemByLine($linenbr);

		if (empty($item)) {
			return false;
		}
		$item->setQty(0);
		$saved = $item->save();

		$this->requestDeleteItem($linenbr);

		if ($saved) {
			return true;
		} else {
			return false;
		}
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return ItemMasterItem
	 * @param  string $itemID Item ID
	 * @return ItemMasterItem
	 */
	public function getItmItem($itemID) {
		return $this->wire('modules')->get('Itm')->item($itemID);
	}

	/**
	 * Return the number of decimal places for qty values
	 * @return int
	 */
	public function decimalPlacesQty() {
		$config = Configs\So::config();
		return $config->decimal_places_qty;
	}

	/**
	 * Return the number of decimal places for price values
	 * @return int
	 */
	public function decimalPlacesPrice() {
		$config = Configs\So::config();
		return $config->decimal_places_price;
	}

	/**
	 * Return Customer
	 * @return Customer
	 */
	public function getCustomer() {
		$q = CustomerQuery::create();
		$q->filterByCustid($this->getCustid());
		return $q->findOne();
	}

	/**
	 * Return Customer Ship-to
	 * @return CustomerShipto
	 */
	public function getCustomerShipto() {
		$q = CustomerShiptoQuery::create();
		$q->filterByCustid($this->getCustid());
		$q->filterByShiptoid($this->getShiptoid());
		return $q->findOne();
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	private function requestDplus(array $data, $addcustID = true) {
		$config  = $this->wire('config');
		$dplusdb = DbDpluso::instance()->dbconfig->dbName;;
		$data = array_merge(["DBNAME=$dplusdb"], $data);

		if ($addcustID) {
			$custID = $this->header->getCustid();
			$custID = !empty($custID) ? $custID : $config->default_webcustomer;
			$data[] = "CUSTID=$custID";
		}
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['default'], $this->sessionID);
	}

	/**
	 * Sends HTTP GET request to send Dplus Item Search Request
	 * @param  string $q Query or Item ID
	 * @return void
	 */
	public function requestItemsearch($q) {
		$data = ["ITNOSRCH=$q"];
		$this->requestDplus($data);
	}

	/**
	 * Request Item Add to cart for CustID
	 * @param string $itemID Item ID
	 * @param float  $qty    Qty to be added
	 * @param string $price  Price to Set
	 */
	private function requestAddItem($itemID, $qty, $price = '') {
		$sanitizer = $this->wire('sanitizer');
		$price = empty($price) ? '' : $sanitizer->float($price, ['precision' => $this->decimalPlacesPrice()]);
		$data = ['CARTDET', "ITEMID=$itemID", "QTY=$qty", "PRICE=$price"];
		$this->requestDplus($data);
	}

	/**
	 * Request Item Add to cart for CustID
	 * @param string $itemID Item ID
	 * @param string $qty    Qty to be added
	 */
	private function requestAddMultipleItems(array $itemIDs, array $qtys) {
		$custID = $this->get_custid();
		$custID = !empty($custID) ? $custID : $config->default_webcustomer;
		$data = array('CARTADDMULTIPLE', "CUSTID=$custID");
		for ($i = 0; $i < sizeof($qtys); $i++) {
			if (!empty($qtys[$i])) {
				$itemID = str_pad($itemIDs[$i], 30, ' ');
				$qty = $qtys[$i];
				$data[] = "ITEMID={$itemID}QTY=$qty";
			}
		}
		$this->requestDplus($data, $addcustID = false);
	}

	/**
	 * Request Item Update
	 * @param string $itemID   Item ID
	 * @param int    $linenbr  Line Number
	 * @param string $custID   Customer ID
	 */
	private function requestUpdateItem($linenbr) {
		$data = ['CARTDET', "LINENO=$linenbr"];
		$this->requestDplus($data);
	}

	/**
	 * Request Item Delete
	 * @param int    $linenbr  Line Number
	 */
	private function requestDeleteItem($linenbr = 1) {
		$data = ['CARTDET', "LINENO=$linenbr", 'QTY=0'];
		$this->requestDplus($data);
	}

	/**
	 * Request cart to be emptied
	 * @return void
	 */
	private function requestEmptyCart() {
		$data = ["EMPTYCART"];
		$this->requestDplus($data);
	}

	/**
	 * Request Sales Order to be created from Cart
	 * @return void
	 */
	private function requestCreateOrder() {
		$data = ["CREATESO"];
		$this->requestDplus($data, $addcustID = false);
	}

	/**
	 * Request Quote to be created from Cart
	 * @return void
	 */
	private function requestCreateQuote() {
		$data = ["CREATEQT"];
		$this->requestDplus($data, $addcustID = false);
	}
}
