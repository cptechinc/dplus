<?php namespace Controllers\Mci\Ci;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use Customer;
use CustomerShiptoQuery, CustomerShipto;
// Dpluso Model
use CustindexQuery;
// ProcessWire Classes, Modules
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Mvc Controllers
use ProcessWire\Wire404Exception;

/**
 * Ci\Shipto
 * Handles Ci Shiptos / Shipto Page
 */
class Shipto extends AbstractSubfunctionController {
	const TITLE          = 'Ship-tos';
	const SUMMARY        = 'View Ship-tos';

/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'custID|text', 'shiptoID|text', 'q|text'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);
		self::decorateInputDataWithCustid($data);
		self::decoratePageWithCustid($data);

		if (empty($data->shiptoID) === false) {
			return self::shipto($data);
		}
		return self::list($data);
	}

	private static function list(WireData $data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$customer = self::getCustomerFromWireData($data);
		$shiptos  = self::getShiptoList($data, $customer);
		self::pw('page')->headline = "CI: $customer->name Ship-tos";
		return self::displayList($data, $customer, $shiptos);
	}

	private static function shipto(WireData $data) {
		if (self::shiptoExists($data->custID, $data->shiptoID) === false) {
			throw new Wire404Exception();
		}
		
		if (self::validateShiptoAccess($data->custID, $data->shiptoID) === false) {
			throw new Wire404Exception();
		}
		
		$shipto = self::getShipto($data->custID, $data->shiptoID);
		$shipto->customer     = self::getCustomer($data->custID);
		$shipto->salesOrders  = self::getShiptoSalesOrders($shipto->custid, $shipto->shiptoid);
		$shipto->salesHistory = self::getShiptoSalesHistory($shipto->custid, $shipto->shiptoid);
		$shipto->quotes       = self::getShiptoQuotes($shipto->custid, $shipto->shiptoid);
		$shipto->contacts     = self::getShiptoContacts($shipto->custid, $shipto->shiptoid);
		self::pw('page')->headline = "CI: {$shipto->customer->name} Ship-to $shipto->shiptoid";
		return self::displayShipto($data, $shipto->customer, $shipto);
	}

/* =============================================================
	Validation
============================================================= */
	// NOTE: KEEP public to be used in Ci\Contacts
	public static function validateShiptoAccess($custID,  $shiptoID) {
		return self::pw('user')->hasCustomer($custID, $shiptoID);
	}


/* =============================================================
	Data Fetching
============================================================= */
	/**
	 * Return List of Customer's Ship-tos
	 * @param  WireData $data
	 * @return PropelModelPager  [CustomerShipto]
	 */
	private static function getShiptoList(WireData $data, Customer $customer) {
		$filter = new Filters\Mar\Shipto();
		$filter->custid($customer->id);
		$filter->user(self::pw('user'));
		$filter->sortby(self::pw('page'));
		$filter->query->orderBy(CustomerShipto::aliasproperty('shiptoID'));
		return $filter->query->paginate(self::pw('input')->pageNum, 0);
	}

	/**
	 * Return if Customer Ship-to exists
	 * @param  string $custID
	 * @param  string $shiptoID
	 * @return bool
	 */
	private static function shiptoExists($custID, $shiptoID) {
		return boolval(CustomerShiptoQuery::create()->filterByCustid($custID)->filterByShiptoid($shiptoID)->count());
	}

	/**
	 * Return CustomerShipto
	 * @param  string $custID
	 * @param  string $shiptoID
	 * @return CustomerShipto
	 */
	private static function getShipto($custID, $shiptoID) {
		return CustomerShiptoQuery::create()->filterByCustid($custID)->filterByShiptoid($shiptoID)->findOne();
	}

	/**
	 * Return Customer / Ship-to related Sales Orders
	 * @param  string $custID     Customer ID
	 * @param  string $shiptoID   Customer Ship-to ID
	 * @param  int    $limit      Number of Results to return
	 * @return PropelModelPager   [SalesOrder]
	 */
	private static function getShiptoSalesOrders($custID, $shiptoID, $limit = 10) {
		$filter = new Filters\Mso\SalesOrder();
		$filter->user(self::pw('user'));
		$filter->custid($custID);
		$filter->shiptoid($shiptoID);
		return $filter->query->paginate(1, $limit);
	}

	/**
	 * Return Customer / Ship-to related Sales History
	 * @param  string $custID     Customer ID
	 * @param  string $shiptoID   Customer Ship-to ID
	 * @param  int    $limit      Number of Results to return
	 * @return PropelModelPager   [Saleshistory]
	 */
	private static function getShiptoSalesHistory($custID, $shiptoID, $limit = 10) {
		$filter = new Filters\Mso\SalesHistory();
		$filter->user(self::pw('user'));
		$filter->custid($custID);
		$filter->shiptoid($shiptoID);
		return $filter->query->paginate(1, $limit);
	}

	/**
	 * Return Customer / Ship-to related Quotes
	 * @param  string $custID     Customer ID
	 * @param  string $shiptoID   Customer Ship-to ID
	 * @param  int    $limit      Number of Results to return
	 * @return PropelModelPager   [Quote]
	 */
	private static function getShiptoQuotes($custID, $shiptoID, $limit = 10) {
		$filter = new Filters\Mqo\Quote();
		$filter->user(self::pw('user'));
		$filter->custid($custID);
		$filter->shiptoid($shiptoID);
		return $filter->query->paginate(1, $limit);
	}

	/**
	 * Return Customer / Ship-to related Contacts
	 * @param  string $custID     Customer ID
	 * @param  string $shiptoID   Customer Ship-to ID
	 * @param  int    $limit      Number of Results to return
	 * @return PropelModelPager   [Custindex]
	 */
	private static function getShiptoContacts($custID, $shiptoID, $limit = 10) {
		$q = CustindexQuery::create();
		$q->filterByCustid($custID);
		$q->filterByShiptoid($shiptoID);
		return $q->paginate(1, 10);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList(WireData $data, Customer $customer, PropelModelPager $shiptos) {
		$html = '';
		$html .= self::renderList($data, $customer, $shiptos);
		return $html;
	}

	
	private static function displayShipto(WireData $data, Customer $customer, CustomerShipto $shipto) {
		$html = '';
		$html .= self::renderShipto($data, $customer, $shipto);
		return $html;
	}
	

/* =============================================================
	Render HTML
============================================================= */
	private static function renderList(WireData $data, Customer $customer, PropelModelPager $shiptos) {
		return self::pw('config')->twig->render('customers/ci/.new/ship-tos/list/display.twig', ['customer' => $customer, 'shiptos' => $shiptos]);
	}

	private static function renderShipto(WireData $data, Customer $customer, CustomerShipto $shipto) {
		return self::pw('config')->twig->render('customers/ci/.new/ship-tos/ship-to/display.twig', ['customer' => $customer, 'shipto' => $shipto]);
	}
}
