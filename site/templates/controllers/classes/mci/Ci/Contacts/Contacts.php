<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;

class Contacts extends Subfunction {
	const PERMISSION_CIO = 'contacts';
	const JSONCODE       = 'ci-contacts';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|text', 'shiptoID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateCustidPermission($data) === false) {
			return self::displayInvalidCustomerOrPermissions($data);
		}

		if (empty($data->shiptoID) === false) {
			if (Shipto::validateShiptoAccess($data) === false) {
				return Shipto::displayInvalidShiptoOrPermissions($data);
			}
		}

		if ($data->refresh) {
			self::requestJson($data);
			self::pw('session')->redirect(self::contactsUrl($data->custID, $data->shiptoID), $http301 = false);
		}

		return self::contacts($data);
	}

	private static function contacts($data) {
		self::getData($data);
		self::pw('page')->headline = "CI: $data->custID Contacts";
		if (empty($data->shiptoID) === false) {
			self::pw('page')->headline = "CI: $data->custID - $data->shiptoID Contacts";
		}
		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::displayContacts($data);
		return $html;
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['custID|text', 'shiptoID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');

		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['custid'] != $data->custID || (array_key_exists('shipid', $json) && $json['shipid'] != $data->shiptoID)) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::contactsUrl($data->custID, $data->shiptoID, $refresh = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ci', 'contacts') > 3) {
			return false;
		}
		$session->setFor('ci', 'contacts', ($session->getFor('ci', 'contacts') + 1));
		$session->redirect(self::contactsUrl($data->custID, $data->shiptoID, $refresh = true), $http301 = false);
	}

/* =============================================================
	Display
============================================================= */

	protected static function displayContacts($data) {
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
		$page->refreshurl = self::contactsUrl($data->custID, $data->shiptoID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$customer = self::getCustomer($data->custID);
		if (empty($data->shiptoID)) {
			return $config->twig->render('customers/ci/contacts/display.twig', ['customer' => $customer, 'json' => $json]);
		}
		$shipto = Shipto::getShipto($data->custID, $data->shiptoID);
		return $config->twig->render('customers/ci/ship-tos/ship-to/contacts/display.twig', ['customer' => $customer, 'shipto' => $shipto, 'json' => $json]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function contactsUrl($custID, $shiptoID = '', $refreshdata = false) {
		$url = new Purl(self::ciContactsUrl($custID, $shiptoID));
		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['custID|text', 'shiptoID|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['CICONTACT', "CUSTID=$vars->custID", "SHIPID=$vars->shiptoID"];
		self::sendRequest($data, $vars->sessionID);
	}
}
