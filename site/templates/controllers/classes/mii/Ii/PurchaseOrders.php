<?php namespace Controllers\Mii\Ii;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ii\PurchaseOrders as Formatter;
// Dplus Document Management
use Dplus\DocManagement\Finders\PurchaseOrder as Docm;

class PurchaseOrders extends Base {
	const JSONCODE          = 'ii-purchase-orders';
	const PERMISSION_IIO    = 'purchaseorders';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data);
			self::pw('session')->redirect(self::ordersUrl($data->itemID), $http301 = false);
		}
		return self::orders($data);
	}

	private static function orders($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		self::getData($data);
		self::pw('page')->headline = "II: $data->itemID Purchase Orders";
		return self::displayOrders($data);
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['itemID|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['IIPURCHORDR', "ITEMID=$vars->itemID"];
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	URLs
============================================================= */
	public static function ordersUrl($itemID, $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('purchase-orders');
		$url->query->set('itemID', $itemID);

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['itemID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$page    = self::pw('page');
		$config  = self::pw('config');
		$session = self::pw('session');
		$html = '';

		if ($jsonm->exists(self::JSONCODE)) {
			if (self::jsonItemidMatches($json['itemid'], $data->itemID) === false) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::ordersUrl($data->itemID, $refresh = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ii', 'purchase-orders') > 3) {
			return false;
		} else {
			$session->setFor('ii', 'purchase-orders', ($session->getFor('ii', 'purchase-orders') + 1));
			$session->redirect(self::ordersUrl($data->itemID, $refresh = true), $http301 = false);
		}
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayOrders($data) {
		$html = '';
		$html .= self::breadCrumbs();;
		$html .= self::displayData($data);
		return $html;
	}

	private static function displayData($data) {
		self::init();
		$jsonm  = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Purchase Orders File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl = self::ordersUrl($data->itemID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$formatter = new Formatter();
		$formatter->init_formatter();
		$docm = new Docm();
		return $config->twig->render('items/ii/purchase-orders/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'module_json' => $jsonm->jsonm, 'docm' => $docm]);
	}

/* =============================================================
	Hooks
============================================================= */
	private static function init() {
		$m = self::pw('modules')->get('DpagesMii');

		$m->addHook('Page(pw_template=ii-item)::documentListUrl', function($event) {
			$itemID   = $event->arguments(0);
			$ponbr    = $event->arguments(1);
			$event->return = Documents::documentsUrlPurchaseorder($itemID, $ponbr);
		});
	}
}
