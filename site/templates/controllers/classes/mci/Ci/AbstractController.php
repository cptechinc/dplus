<?php namespace Controllers\Mci\Ci;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use Customer;
// ProcessWire
Use ProcessWire\Cio;
Use ProcessWire\Page;
Use ProcessWire\User;
Use ProcessWire\WireData;
// Dplus
use Dplus\Mar\Armain\Cmm;
// MVC Controllers
use Controllers\Templates\AbstractController as Controller;

abstract class AbstractController extends Controller {
	const DPLUSPERMISSION = 'ci';
	const PERMISSION_CIO  = '';
	const TITLE      = 'Customer Information';
	const SUMMARY    = 'View Customer Information';

/* =============================================================
	1. Indexes
============================================================= */

/* =============================================================
	2. Validations
============================================================= */
	/**
	 * Validate User's Permission to this Function
	 * @param  User|null $user
	 * @return bool
	 */
	public static function validateUserPermission(User $user = null) {
		if (parent::validateUserPermission($user) === false) {
			return false;
		}
		if (empty(static::PERMISSION_CIO)) {
			return true;
		}
		$cio  = self::getCio();
		if (empty($user)) {
			$user = self::pw('user');
		}
		return $cio->allowUser($user, static::PERMISSION_CIO);
	}

	/**
	 * Validate Customer By Record Position
	 * @param  int  $rID
	 * @return bool
	 */
	public static function validateCustomerByRid($rID) {
		if (is_numeric($rID) === false) {
			return false;
		}
		return Cmm::instance()->existsByRid($rID);
	}

	/**
	 * Validate Customer By ID
	 * @param  string  $id
	 * @return bool
	 */
	public static function validateCustomerById($id) {
		return Cmm::instance()->exists($id);
	}

	/**
	 * Return If User Has Customer
	 * @param  User|null $user
	 * @param  string   $custID
	 * @return bool
	 */
	public static function validateUserHasCustomerPermission(User $user = null, $custID) {
		if (empty($user)) {
			$user = self::pw('user');
		}
		return $user->has_customer($custID);
	}

/* =============================================================
	3. Data Fetching / Requests / Retrieval
============================================================= */
	/**
	 * Return Customer By Record ID
	 * @param  int      $rID  Customer Record ID
	 * @return Customer
	 */
	public static function getCustomerByRid($rID) {
		return Cmm::instance()->customerByRid($rID);
	}

	/**
	 * Return Customer ID By Record ID
	 * @param  int      $rID  Customer Record ID
	 * @return string
	 */
	public static function getCustidByRid($rID) {
		return Cmm::instance()->custidByRid($rID);
	}

	public static function getCustomer($id) {
		if (self::pw('config')->ci->useRid) {
			return Cmm::instance()->customerByRid($id);
		}
		return Cmm::instance()->customer($id);
	}

	public static function getCustomerFromWireData(WireData $data) {
		if ($data->rid) {
			return Cmm::instance()->customerByRid($data->rid);
		}
		return Cmm::instance()->customer($data->custID);
	}

/* =============================================================
	4. URLs
============================================================= */
	/**
	 * Return URL to CI Page
	 * @return string
	 */
	public static function url() {
		return self::pw('pages')->get('pw_template=ci')->url;
	}

	/**
	 * Return URL to Customer Page
	 * @param  int|string     $id   Customer Record ID  / Cust ID
	 * @return string
	 */
	public static function ciUrl($id) {
		if (self::pw('config')->ci->useRid === false) {
			return self::ciCustidUrl($id);
		}
		if (is_int($id)) {
			return self::ciRidUrl($id);
		}
		return self::ciRidUrl(Cmm::instance()->ridByCustid($id));
	}

	public static function ciRidUrl(int $rID) {
		return static::url()."?rid=$rID";
	}

	public static function ciCustidUrl($id) {
		$url = new Purl(static::url());
		$url->query->set('custID', $id);
		return $url->getUrl();
	}

	/**
	 * Return URL to Customer Page
	 * @param  int     $rID   Customer Record ID  
	 * @return string
	 */
	public static function custUrl($id) {
		return static::ciUrl($id);
	}

	/**
	 * Return URL to Customer Subfunction Page
	 * @param  int|string     $id   Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciSubfunctionUrl($id, $sub) {
		$url = new Purl(self::ciUrl($id));
		$url->path->add($sub);
		return $url->getUrl();
	}

	/**
	 * Return URL to Customer Contacts Page
	 * @param  int|string     $id   Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciContactsUrl($id, $shiptoID = '') {
		$url = new Purl(self::ciUrl($id));
		$url->path->add('contacts');
		if ($shiptoID) {
			$url->query->set('shiptoID', $shiptoID);
		}
		return $url->getUrl();
	}

	/**
	 * Return URL to Customer Contact Page
	 * @param  int|string  $id        Customer Record ID  | Customer IO
	 * @param  string      $shiptoID   Customer Ship-to ID
	 * @param  string      $contactID  Contact ID
	 * @return string
	 */
	public static function ciContactUrl($id, $shiptoID = '', $contactID) {
		$url = new Purl(self::ciContactsUrl($id, $shiptoID));
		$url->path->add('contact');
		$url->query->set('contactID', $contactID);
		return $url->getUrl();
	}

	/**
	 * Return URL to Customer Contact Edit Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @param  string      $shiptoID   Customer Ship-to ID
	 * @param  string      $contactID  Contact ID
	 * @return string
	 */
	public static function ciContactEditUrl($id, $shiptoID = '', $contactID) {
		$url = new Purl(self::ciContactUrl($id, $shiptoID, $contactID));
		$url->path->add('edit');
		return $url->getUrl();
	}

	/**
	 * Return URL to CI Pricing Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciPricingUrl($id) {
		return self::ciSubfunctionUrl($id, 'pricing');
	}


	/**
	 * Return to Customer Ship-to Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @param  string      $shiptoID    Customer Ship-to ID
	 * @return string
	 */
	public static function ciShiptoUrl($id, $shiptoID = '') {
		$url = new Purl(self::ciUrl($id));
		$url->path->add('ship-tos');
		if ($shiptoID) {
			$url->query->set('shiptoID', $shiptoID);
		}
		return $url->getUrl();
	}

	/**
	 * Return URL to CI Sales ORders Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciSalesOrdersUrl($id) {
		return self::ciSubfunctionUrl($id, 'sales-orders');
	}

	/**
	 * Return URL to CI Sales History Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciSalesHistoryUrl($id) {
		return self::ciSubfunctionUrl($id, 'sales-history');
	}

	/**
	 * Return URL to CI Purchase Orders Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciPurchaseOrdersUrl($id) {
		return self::ciSubfunctionUrl($id, 'purchase-orders');
	}

	/**
	 * Return URL to CI Quotes Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciQuotesUrl($id) {
		return self::ciSubfunctionUrl($id, 'quotes');
	}

	/**
	 * Return URL to CI Open Invoices Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciOpenInvoicesUrl($id) {
		return self::ciSubfunctionUrl($id, 'open-invoices');
	}

	/**
	 * Return URL to CI Payments Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciPaymentsUrl($id) {
		return self::ciSubfunctionUrl($id, 'payments');
	}

	/**
	 * Return URL to CI Credit Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciCreditUrl($id) {
		return self::ciSubfunctionUrl($id, 'credit');
	}

	/**
	 * Return URL to CI Standing Orders Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciStandingOrdersUrl($id) {
		return self::ciSubfunctionUrl($id, 'standing-orders');
	}

	/**
	 * Return URL to CI Documents Page
	 * @param  int|string  $id         Customer Record ID  | Customer IO
	 * @return string
	 */
	public static function ciDocumentsUrl($id) {
		return self::ciSubfunctionUrl($id, 'documents');
	}

/* =============================================================
	5. Displays
============================================================= */

/* =============================================================
	6. HTML Rendering
============================================================= */
	/**
	 * Render Invalid Customer Alert
	 * @param  WireData $data
	 * @return string
	 */
	protected static function renderInvalidCustomer(WireData $data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Customer not found"]);
	}

/* =============================================================
	7. Class / Module Getters
============================================================= */
	/** @return Cio */
	public static function getCio() {
		return self::pw('modules')->get('Cio');
	}

/* =============================================================
	8. Supplemental
============================================================= */
	/**
	 * Return Path to JS directory for this class
	 * @return string
	 */
	protected static function jsPath() {
		$scriptPath = 'scripts/pages/';
		$scriptPath .= str_replace('\\', '/', ltrim(strtolower(static::class), 'controllers\\')) . '/';
		return $scriptPath;
	}

/* =============================================================
	9. Hooks / Object Decorating
============================================================= */
	public static function initHooks() {

	}
	
	/**
	 * Add CustID to Data
	 * @param  WireData $data
	 * @return true
	 */
	protected static function decorateInputDataWithCustid(WireData $data) {
		self::sanitizeParametersShort($data, ['rid|int']);

		if ($data->custID) {
			return true;
		}

		if (self::pw('config')->ci->useRid && $data->rid > 0) {
			$data->custID = self::getCustidByRid($data->rid);
			return true;
		}
		return false;
	}

	/**
	 * Add CustID to page object
	 * @param  WireData  $data
	 * @param  Page|null $page
	 * @return bool
	 */
	protected static function decoratePageWithCustid(WireData $data, Page $page = null) {
		$page = $page ? $page : self::pw('page');

		if ($data->has('custID') === false) {
			self::decorateInputDataWithCustid($data);
		}
		if (empty($data->custID)) {
			return false;
		}
		$page->custid = $data->custID;
		return true;
	}
}
