<?php namespace Controllers\Mvi\Vi;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use VendorQuery, Vendor;
// Dplus Code Validators
use Dplus\CodeValidators as Validators;
// MVC Controllers
use Mvc\Controllers\Controller;
// Dplus User Options
use Dplus\UserOptions\Mvi\Vio;

abstract class Base extends Controller {
	const PERMISSION     = 'vi';
	const PERMISSION_VIO = '';

/* =============================================================
	URLs
============================================================= */
	public static function viUrl($vendorID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=vi')->url);
		if ($vendorID) {
			$url->query->set('vendorID', $vendorID);
		}
		return $url->getUrl();
	}

	public static function viShipfromUrl($vendorID, $shipfromID = '') {
		$url = new Purl(self::viUrl($vendorID));
		$url->path->add('ship-froms');
		if ($shipfromID) {
			$url->query->set('shipfromID', $shipfromID);
		}
		return $url->getUrl();
	}

	public static function viSubfunctionUrl($vendorID, $sub) {
		$url = new Purl(self::viUrl($vendorID));
		$url->path->add($sub);
		return $url->getUrl();
	}

	public static function viContactsUrl($vendorID, $shipfromID = '') {
		$url = new Purl(self::viUrl($vendorID));
		$url->path->add('contacts');
		$url->query->set('shipfromID', $shipfromID);
		return $url->getUrl();
	}

	public static function viDocumentsUrl($vendorID) {
		$url = new Purl(self::viUrl($vendorID));
		$url->path->add('documents');
		return $url->getUrl();
	}

	public static function viPurchaseOrdersUrl($vendorID) {
		$url = new Purl(self::viUrl($vendorID));
		$url->path->add('purchase-orders');
		return $url->getUrl();
	}

	public static function viPurchaseOrdersUnreleasedUrl($vendorID) {
		$url = new Purl(self::viPurchaseOrdersUrl($vendorID));
		$url->path->add('unreleased');
		return $url->getUrl();
	}

	public static function viPurchaseOrdersUninvoicedUrl($vendorID) {
		$url = new Purl(self::viPurchaseOrdersUrl($vendorID));
		$url->path->add('uninvoiced');
		return $url->getUrl();
	}

	public static function viPurchaseHistoryUrl($vendorID) {
		$url = new Purl(self::viUrl($vendorID));
		$url->path->add('purchase-history');
		return $url->getUrl();
	}

	public static function viOpenInvoicesUrl($vendorID) {
		$url = new Purl(self::viUrl($vendorID));
		$url->path->add('open-invoices');
		return $url->getUrl();
	}

	public static function viPaymentsUrl($vendorID) {
		$url = new Purl(self::viUrl($vendorID));
		$url->path->add('payments');
		return $url->getUrl();
	}

	public static function viSummaryUrl($vendorID) {
		$url = new Purl(self::viUrl($vendorID));
		$url->path->add('summary');
		return $url->getUrl();
	}

	public static function viCostingUrl($vendorID) {
		$url = new Purl(self::viUrl($vendorID));
		$url->path->add('costing');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	protected static function displayInvalidVendorOrPermissions($data) {
		if (self::validateVendorid($data->vendorID) === false) {
			return self::displayInvalidVendorid($data);
		}

		if (self::validateUserPermission($data)) {
			return self::displayInvalidPermission($data);
		}
		return '';
	}

	protected static function displayInvalidVendorid($data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Vendor $data->vendorID not found"]);
	}

	protected static function displayInvalidPermission($data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Access Denied', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You don't have access to this function"]);
	}

	protected static function displayBreadCrumbs($data) {
		return self::pw('config')->twig->render('vendors/vi/bread-crumbs.twig');
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getVio() {
		return Vio::getInstance();
	}

	public static function getValidator() {
		return new Validators\Map();
	}

	public static function getVendor($vendorID) {
		return VendorQuery::create()->findOneById($vendorID);
	}

	public static function validateVendoridPermission($data) {
		self::sanitizeParametersShort($data, ['vendorID|text']);
		$user = self::pw('user');

		if (self::validateVendorid($data->vendorID) === false) {
			return false;
		}

		if ($user->has_customer($data->vendorID) === false) {
			return false;
		}

		if (self::validateUserPermission($data) === false) {
			return false;
		}
		return true;
	}

	public static function validateVendorid($vendorID) {
		$validate = self::getValidator();
		return $validate->vendorid($vendorID);
	}

	protected static function validateUserPermission($data) {
		$user = self::pw('user');

		if ($user->has_function(static::PERMISSION) === false) {
			return false;
		}

		$vio  = self::getVio();
		return $vio->allowUser($user, static::PERMISSION_VIO);
	}
}
