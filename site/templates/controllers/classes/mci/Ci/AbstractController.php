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
		return Cmm::instance()->customerByRid($rID);;
	}

	/**
	 * Return Customer ID By Record ID
	 * @param  int      $rID  Customer Record ID
	 * @return string
	 */
	public static function getCustidByRid($rID) {
		return Cmm::instance()->custidByRid($rID);;
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
	 * @param  int     $rID   Customer Record ID  
	 * @return string
	 */
	public static function ciUrl(int $rID) {
		return static::url()."?rid=$rID";
	}

	/**
	 * Return URL to Customer Page
	 * @param  int     $rID   Customer Record ID  
	 * @return string
	 */
	public static function custUrl(int $rID) {
		return static::url()."?rid=$rID";
	}

	/**
	 * Return URL to Customer Subfunction Page
	 * @param  int     $rID   Customer Record ID  
	 * @return string
	 */
	public static function ciSubfunctionUrl(int $rID, $sub) {
		$url = new Purl(self::ciUrl($rID));
		$url->path->add($sub);
		return $url->getUrl();
	}

	/**
	 * Return URL to Customer Contacts Page
	 * @param  int     $rID   Customer Record ID  
	 * @return string
	 */
	public static function ciContactsUrl(int $rID, $shiptoID = '') {
		$url = new Purl(self::ciUrl($rID));
		$url->path->add('contacts');
		if ($shiptoID) {
			$url->query->set('shiptoID', $shiptoID);
		}
		return $url->getUrl();
	}

	/**
	 * Return URL to Customer Contact Page
	 * @param  int     $rID        Customer Record ID  
	 * @param  string  $shiptoID   Customer Ship-to ID
	 * @param  string  $contactID  Contact ID
	 * @return string
	 */
	public static function ciContactUrl($rID, $shiptoID = '', $contactID) {
		$url = new Purl(self::ciContactsUrl($rID, $shiptoID));
		$url->path->add('contact');
		$url->query->set('contactID', $contactID);
		return $url->getUrl();
	}

	/**
	 * Return URL to Customer Contact Edit Page
	 * @param  int     $rID        Customer Record ID  
	 * @param  string  $shiptoID   Customer Ship-to ID
	 * @param  string  $contactID  Contact ID
	 * @return string
	 */
	public static function ciContactEditUrl($rID, $shiptoID = '', $contactID) {
		$url = new Purl(self::ciContactUrl($rID, $shiptoID, $contactID));
		$url->path->add('edit');
		return $url->getUrl();
	}

	/**
	 * Return URL to CI Pricing Page
	 * @param  int     $rID        Customer Record ID  
	 * @return string
	 */
	public static function ciPricingUrl($rID) {
		return self::ciSubfunctionUrl($rID, 'pricing');
	}


	/**
	 * Return to Customer Ship-to Page
	 * @param  int    $rID        Customer Record ID  
	 * @param  string $shiptoID    Customer Ship-to ID
	 * @return string
	 */
	public static function ciShiptoUrl($rID, $shiptoID = '') {
		$url = new Purl(self::ciUrl($rID));
		$url->path->add('ship-tos');
		if ($shiptoID) {
			$url->query->set('shiptoID', $shiptoID);
		}
		return $url->getUrl();
	}

	/**
	 * Return URL to CI Sales ORders Page
	 * @param  int     $rID        Customer Record ID  
	 * @return string
	 */
	public static function ciSalesOrdersUrl($rID) {
		return self::ciSubfunctionUrl($rID, 'sales-orders');
	}

	/**
	 * Return URL to CI Sales History Page
	 * @param  int     $rID        Customer Record ID  
	 * @return string
	 */
	public static function ciSalesHistoryUrl($rID) {
		return self::ciSubfunctionUrl($rID, 'sales-history');
	}

	/**
	 * Return URL to CI Purchase Orders Page
	 * @param  int     $rID        Customer Record ID  
	 * @return string
	 */
	public static function ciPurchaseOrdersUrl($rID) {
		return self::ciSubfunctionUrl($rID, 'purchase-orders');
	}

	/**
	 * Return URL to CI Quotes Page
	 * @param  int     $rID        Customer Record ID  
	 * @return string
	 */
	public static function ciQuotesUrl($rID) {
		return self::ciSubfunctionUrl($rID, 'quotes');
	}

	/**
	 * Return URL to CI Open Invoices Page
	 * @param  int     $rID        Customer Record ID  
	 * @return string
	 */
	public static function ciOpenInvoicesUrl($rID) {
		return self::ciSubfunctionUrl($rID, 'open-invoices');
	}

	/**
	 * Return URL to CI Payments Page
	 * @param  int     $rID        Customer Record ID  
	 * @return string
	 */
	public static function ciPaymentsUrl($rID) {
		return self::ciSubfunctionUrl($rID, 'payments');
	}

	/**
	 * Return URL to CI Credit Page
	 * @param  int     $rID        Customer Record ID  
	 * @return string
	 */
	public static function ciCreditUrl($rID) {
		return self::ciSubfunctionUrl($rID, 'credit');
	}

	/**
	 * Return URL to CI Standing Orders Page
	 * @param  int     $rID        Customer Record ID  
	 * @return string
	 */
	public static function ciStandingOrdersUrl($rID) {
		return self::ciSubfunctionUrl($rID, 'standing-orders');
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

		if ($data->rid > 0) {
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
