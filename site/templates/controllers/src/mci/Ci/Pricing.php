<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Controllers
use Controllers\Mii\Ii;

class Pricing extends Subfunction {
	const PERMISSION_CIO = 'pricing';
	const JSONCODE       = 'ii-pricing';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|text', 'itemID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateCustidPermission($data) === false) {
			return self::displayInvalidCustomerOrPermissions($data);
		}

		if (empty($data->itemID)) {
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
			return self::displayItemidForm($data);
		}

		if ($data->refresh) {
			self::requestJson($data);
			self::pw('session')->redirect(self::pricingUrl($data->custID, $data->itemID), $http301 = false);
		}

		return self::pricing($data);
	}

	private static function pricing($data) {
		self::getData($data);
		self::pw('page')->headline = "CI: $data->custID Pricing";
		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::displayPricing($data);
		return $html;
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['custID|text', 'itemID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');


		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['itemid'] != $data->itemID || $json['custid'] != $data->custID) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::pricingUrl($data->custID, $data->itemID, $refresh = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ci', 'pricing') > 3) {
			return false;
		}
		$session->setFor('ci', 'pricing', ($session->getFor('ci', 'pricing') + 1));
		$session->redirect(self::pricingUrl($data->custID, $data->itemID, $refresh = true), $http301 = false);
	}

/* =============================================================
	Display
============================================================= */
	private static function displayItemidForm($data) {
		self::pw('page')->js .= self::pw('config')->twig->render('customers/ci/pricing/item-form.js.twig');
		return self::pw('config')->twig->render('customers/ci/pricing/item-form.twig');
	}

	protected static function displayPricing($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Pricing File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl = self::pricingUrl($data->custID, $data->itemID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$customer = self::getCustomer($data->custID);
		return $config->twig->render('customers/ci/pricing/display.twig', ['item' => Ii\Pricing::getItmItem($data->itemID), 'customer' => $customer, 'json' => $json]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function pricingUrl($custID, $itemID = '', $refreshdata = false) {
		$url = new Purl(self::ciPricingUrl($custID));

		if ($itemID) {
			$url->query->set('itemID', $itemID);

			if ($refreshdata) {
				$url->query->set('refresh', 'true');
			}
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['itemID|text', 'custID|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['CIPRICE', "ITEMID=$vars->itemID", "CUSTID=$vars->custID"];
		self::sendRequest($data, $vars->sessionID);
	}
}
