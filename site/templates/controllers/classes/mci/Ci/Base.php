<?php namespace Controllers\Mci\Ci;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Code Validators
use Dplus\CodeValidators as Validators;
// MVC Controllers
use Mvc\Controllers\AbstractController;

abstract class Base extends AbstractController {
	const PERMISSION     = 'ci';
	const PERMISSION_IIO = '';

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
		$url->path->add('shiptos');
		if ($shiptoID) {
			$url->query->set('shiptoID', $custID);
		}
		return $url->getUrl();
	}

	public static function ciSubfunctionUrl($custID, $sub) {
		$url = new Purl(self::pw('pages')->get('pw_template=ci')->url);
		$url->path->add($sub);
		$url->query->set('custID', $custID);
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

/* =============================================================
	Supplemental
============================================================= */
	public static function getCio() {
		return self::pw('modules')->get('Cio');
	}

	public static function getValidator() {
		return new Validators\Mar();
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

		if ($user->has_function(self::PERMISSION) === false) {
			return false;
		}

		if ($cio->allowUser($user, static::PERMISSION_IIO) === false) {
			return false;
		}
		return true;
	}
}
