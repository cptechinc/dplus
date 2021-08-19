<?php namespace Controllers\Mci\Ci;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use CustomerQuery, Customer;
// Dplus Code Validators
use Dplus\CodeValidators as Validators;
// MVC Controllers
use Mvc\Controllers\AbstractController;

abstract class Base extends AbstractController {
	const PERMISSION     = 'ci';
	const PERMISSION_CIO = '';

/* =============================================================
	URLs
============================================================= */
	public static function ciUrl($custID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=ci')->url);
		if ($custID) {
			$url->query->set('custID', $custID);
		}
		return $url->getUrl();
	}

	public static function ciShiptoUrl($custID, $shiptoID = '') {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('ship-tos');
		if ($shiptoID) {
			$url->query->set('shiptoID', $shiptoID);
		}
		return $url->getUrl();
	}

	public static function ciSubfunctionUrl($custID, $sub) {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add($sub);
		return $url->getUrl();
	}

	public static function ciPricingUrl($custID) {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('pricing');
		return $url->getUrl();
	}

	public static function ciContactsUrl($custID, $shiptoID = '') {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('contacts');
		$url->query->set('shiptoID', $shiptoID);
		return $url->getUrl();
	}

	public static function ciContactUrl($custID, $shiptoID = '', $contactID) {
		$url = new Purl(self::ciContactsUrl($custID, $shiptoID));
		$url->path->add('contact');
		$url->query->set('contactID', $contactID);
		return $url->getUrl();
	}

	public static function ciContactEditUrl($custID, $shiptoID = '', $contactID) {
		$url = new Purl(self::ciContactUrl($custID, $shiptoID, $contactID));
		$url->path->add('edit');
		return $url->getUrl();
	}

	public static function ciSalesordersUrl($custID) {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('sales-orders');
		return $url->getUrl();
	}

	public static function ciSaleshistoryUrl($custID) {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('sales-history');
		return $url->getUrl();
	}

	public static function ciDocumentsUrl($custID) {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('documents');
		return $url->getUrl();
	}

	public static function ciPurchaseordersUrl($custID) {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('purchase-orders');
		return $url->getUrl();
	}

	public static function ciQuotesUrl($custID) {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('quotes');
		return $url->getUrl();
	}

	public static function ciOpenInvoicesUrl($custID) {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('open-invoices');
		return $url->getUrl();
	}

	public static function ciPaymentsUrl($custID) {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('payments');
		return $url->getUrl();
	}

	public static function ciCreditUrl($custID) {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('credit');
		return $url->getUrl();
	}

	public static function ciStandingOrdersUrl($custID) {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('standing-orders');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	protected static function displayInvalidCustomerOrPermissions($data) {
		if (self::validateCustid($data->custID) === false) {
			return self::displayInvalidCustid($data);
		}

		if ($user->has_customer($data->custID) === false) {
			return self::displayUserNotAllowedCustomer($data);
		}

		if (self::validateUserPermission($data)) {
			return self::displayInvalidPermission($data);
		}
		return '';
	}

	protected static function displayInvalidCustid($data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Customer $data->custID not found"]);
	}

	protected static function displayUserNotAllowedCustomer($data) {
		$config = self::pw('config');
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Access Denied', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You do not have permission to access to $data->custID"]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('customers/search-form.twig');
		return $html;
	}

	protected static function displayInvalidPermission($data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Access Denied', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You don't have access to this function"]);
	}

	protected static function displayBreadCrumbs($data) {
		return self::pw('config')->twig->render('customers/ci/bread-crumbs.twig');
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getCio() {
		return self::pw('modules')->get('Cio');
	}

	public static function getValidator() {
		return new Validators\Mar();
	}

	public static function getCustomer($custID) {
		return CustomerQuery::create()->findOneById($custID);
	}

	public static function validateCustidPermission($data) {
		self::sanitizeParametersShort($data, ['custID|text']);
		$user = self::pw('user');

		if (self::validateCustid($data->custID) === false) {
			return false;
		}

		if ($user->has_customer($data->custID) === false) {
			return false;
		}

		if (self::validateUserPermission($data) === false) {
			return false;
		}
		return true;
	}

	public static function validateCustid($custID) {
		$validate = self::getValidator();
		return $validate->custid($custID);
	}

	protected static function validateUserPermission($data) {
		$user = self::pw('user');
		$cio  = self::getCio();

		if ($user->has_function(static::PERMISSION) === false) {
			return false;
		}

		if ($cio->allowUser($user, static::PERMISSION_CIO) === false) {
			return false;
		}
		return true;
	}
}
