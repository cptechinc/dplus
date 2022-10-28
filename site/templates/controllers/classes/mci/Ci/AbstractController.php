<?php namespace Controllers\Mci\Ci;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use Customer;
// ProcessWire
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
	URLs
============================================================= */
	public static function url() {
		return self::pw('pages')->get('pw_template=ci')->url;
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
		$url = new Purl(self::custUrl($rID));
		$url->path->add($sub);
		return $url->getUrl();
	}

	/**
	 * Return URL to Customer Contacts Page
	 * @param  int     $rID   Customer Record ID  
	 * @return string
	 */
	public static function ciContactsUrl(int $rID) {
		$url = new Purl(self::custUrl($rID));
		$url->path->add('contacts');
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

/* =============================================================
	HTML Rendering
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
	Validation Functions
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
	Data Fetching
============================================================= */
	/**
	 * Return Customer By Record ID
	 * @param  string $rID
	 * @return Customer
	 */
	public static function getCustomerByRid($rID) {
		return Cmm::instance()->customerByRid($rID);;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getCio() {
		return self::pw('modules')->get('Cio');
	}
}
