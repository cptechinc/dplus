<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;


class Credit extends Subfunction {
	const PERMISSION_CIO = 'credit';
	const JSONCODE       = 'ci-credit';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateCustidPermission($data) === false) {
			return self::displayInvalidCustomerOrPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data);
			self::pw('session')->redirect(self::creditUrl($data->custID), $http301 = false);
		}
		return self::credit($data);
	}

	private static function credit($data) {
		self::getData($data);
		self::pw('page')->headline = "CI: $data->custID Credit";
		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::displayCredit($data);
		return $html;
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['custID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');


		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['custid'] != $data->custID) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::creditUrl($data->custID, $refresh = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ci', 'credit') > 3) {
			return false;
		}
		$session->setFor('ci', 'credit', ($session->getFor('ci', 'credit') + 1));
		$session->redirect(self::creditUrl($data->custID, $refresh = true), $http301 = false);
	}

/* =============================================================
	Display
============================================================= */
	protected static function displayCredit($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Quotes File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl   = self::creditUrl($data->custID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$customer  = self::getCustomer($data->custID);
		return $config->twig->render('customers/ci/credit/display.twig', ['customer' => $customer, 'json' => $json]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function creditUrl($custID, $refreshdata = false) {
		$url = new Purl(self::ciCreditUrl($custID));

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['custID|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['CICREDIT', "CUSTID=$vars->custID"];
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	Supplemental
============================================================= */

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMci');
	}
}
