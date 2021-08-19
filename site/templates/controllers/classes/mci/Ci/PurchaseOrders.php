<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ci\SalesOrders as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;

class PurchaseOrders extends Subfunction {
	const PERMISSION_CIO = 'customerpo';
	const JSONCODE       = 'ci-purchase-orders';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|text', 'custpo|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateCustidPermission($data) === false) {
			return self::displayInvalidCustomerOrPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data);
			self::pw('session')->redirect(self::ordersUrl($data->custID, $data->custpo), $http301 = false);
		}

		if (empty($data->custpo)) {
			$jsonm  = self::getJsonModule();
			$jsonm->delete(SalesHistory::JSONCODE);
			$jsonm->delete(SalesOrders::JSONCODE);
			return self::displayPoForm($data);
		}
		return self::orders($data);
	}

	private static function orders($data) {
		self::getData($data);
		self::pw('page')->headline = "CI: $data->custID Orders that match '$data->custpo'";
		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::displayCustpo($data);
		return $html;
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['custID|text', 'custpo|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(SalesHistory::JSONCODE);
		$session = self::pw('session');

		if ($jsonm->exists(SalesHistory::JSONCODE)) {
			if ($json['custid'] != $data->custID) {
				$jsonm->delete(SalesHistory::JSONCODE);
				$session->redirect(self::ordersUrl($data->custID, $data->custpo, $refresh = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ci', 'purchase-orders') > 3) {
			return false;
		}
		$session->setFor('ci', 'purchase-orders', ($session->getFor('ci', 'purchase-orders') + 1));
		$session->redirect(self::ordersUrl($data->custID, $data->custpo, $refresh = true), $http301 = false);
	}

/* =============================================================
	Display
============================================================= */
	protected static function displayCustpo($data) {
		$jsonm  = self::getJsonModule();
		$page = self::pw('page');
		$page->refreshurl   = self::ordersUrl($data->custID, $data->custpo, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(SalesHistory::JSONCODE);
		$customer  = self::getCustomer($data->custID);
		self::initHooks();

		$html = new WireData();
		$html->history = self::displaySaleshistory($data);
		$html->orders= self::displaySalesorders($data);
		return self::pw('config')->twig->render('customers/ci/purchase-orders/display.twig', ['customer' => $customer, 'html' => $html]);
	}

	private static function displaySaleshistory($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(SalesHistory::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(SalesHistory::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Sales History File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}

		$formatter = SalesHistory::getFormatter();
		$docm      = SalesHistory::getDocFinder();
		return $config->twig->render('customers/ci/sales-history/sales-history.twig', ['json' => $json, 'module_formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

	private static function displaySalesorders($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(SalesOrders::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(SalesOrders::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Sales Orders File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}

		$formatter = SalesOrders::getFormatter();
		$docm      = SalesOrders::getDocFinder();
		return $config->twig->render('customers/ci/sales-orders/sales-orders.twig', ['json' => $json, 'module_formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

	private static function displayPoForm($data) {
		return self::pw('config')->twig->render('customers/ci/purchase-orders/po-form.twig');
	}

/* =============================================================
	URLs
============================================================= */
	public static function ordersUrl($custID, $custpo = '', $refreshdata = false) {
		$url = new Purl(self::ciPurchaseordersUrl($custID));

		if ($custpo) {
			$url->query->set('custpo', $custpo);

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
		$fields = ['custID|text', 'shiptoID|text', 'custpo|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['CICUSTPO', "CUSTID=$vars->custID", "SHIPID=$vars->shiptoID", "CUSTPO=$vars->custpo"];
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	Supplemental
============================================================= */
	protected static function getFormatter() {
		$f = new Formatter();
		$f->init_formatter();
		return $f;
	}

	protected static function getDocFinder() {
		return new DocFinders\SalesOrder();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMci');

		$m->addHook('Page(pw_template=ci)::documentListUrl', function($event) {
			$event->return = Documents::documentsUrlSalesorder($event->arguments(0), $event->arguments(1));
		});
	}
}
