<?php namespace Controllers\Mii\Ii;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Model
use CustomerQuery, Customer;

class Pricing extends Base {
	const JSONCODE       = 'ii-pricing';
	const PERMISSION_IIO = 'pricing';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool', 'custID|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data, session_id());
			self::pw('session')->redirect(self::pricingUrl($data->itemID, $data->custID), $http301 = false);
		}

		if (empty($data->custID) === false) {
			return self::pricing($data);
		}
		return self::initScreen($data);
	}

	private static function pricing($data) {
		self::getData($data);
		self::pw('page')->headline = "$data->itemID Pricing";
		return self::displayPricing($data);
	}

	private static function initScreen($data) {
		$config = self::pw('config');
		$page = self::pw('page');

		$page->headline = "II: $data->itemID Pricing";
		$page->js = $config->twig->render('items/ii/pricing/customer/form.js.twig');
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		return self::displayInitScreen($data);
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['itemID|text', 'custID|text', 'sessionID|text'];
		$vars = self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['IIPRICE', "ITEMID=$vars->itemID"];
		if ($vars->custID) {
			$data[] = "CUSTID=$vars->custID";
		}
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	URLs
============================================================= */
	public static function pricingUrl($itemID, $custID = '', $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('pricing');
		$url->query->set('itemID', $itemID);

		if ($custID) {
			$url->query->set('custID', $custID);

			if ($refreshdata) {
				$url->query->set('refresh', 'true');
			}
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['itemID|text', 'custID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');

		if ($jsonm->exists(self::JSONCODE)) {
			if (self::jsonItemidMatches($json['itemid'], $data->itemID) === false || $json['custid'] != $data->custID) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::pricingUrl($data->itemID, $data->custID, $refresh = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ii', 'pricing') > 3) {
			return false;
		}
		$session->setFor('ii', 'pricing', ($session->getFor('ii', 'pricing') + 1));
		$session->redirect(self::pricingUrl($data->itemID, $data->custID, $refresh = true), $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayPricing($data) {
		$html = '';
		$html .= self::breadCrumbs();;
		$html .= self::displayData($data);
		return $html;
	}

	private static function displayData($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Pricing File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$iim  = self::pw('modules')->get('DpagesMii');
		$page = self::pw('page');
		$page->refreshurl = self::pricingUrl($data->itemID, $data->custID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$customer = CustomerQuery::create()->findOneByCustid($data->custID);
		return $config->twig->render('items/ii/pricing/display.twig', ['item' => self::getItmItem($data->itemID), 'customer' => $customer, 'json' => $json]);
	}

	private static function displayInitScreen($data) {
		$html = self::breadCrumbs();
		$html .= self::pw('config')->twig->render('items/ii/pricing/customer/form.twig', ['itemID' => $data->itemID]);
		return $html;
	}
}
