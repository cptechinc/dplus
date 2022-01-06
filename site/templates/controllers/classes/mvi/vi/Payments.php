<?php namespace Controllers\Mvi\Vi;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Vi\Payments as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;

class Payments extends Subfunction {
	const PERMISSION_VIO = '';
	const JSONCODE       = 'vi-payments';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['vendorID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateVendorid($data->vendorID) === false) {
			self::pw('session')->redirect(self::viUrl(), $http301 = false);
		}

		if (self::validateVendoridPermission($data) === false) {
			return self::displayInvalidVendorOrPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data);
			self::pw('session')->redirect(self::paymentsUrl($data->vendorID), $http301 = false);
		}
		return self::payments($data);
	}

	private static function payments($data) {
		self::getData($data);
		self::pw('page')->headline = "VI: $data->vendorID Payments";

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::display($data);
		return $html;
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['vendorID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');

		if ($session->getFor('vi', 'payments') > 3 && $json['vendid'] == $data->vendorID) {
			return false;
		}
		$session->setFor('vi', 'payments', ($session->getFor('vi', 'payments') + 1));
		$session->redirect(self::paymentsUrl($data->vendorID, $refresh = true), $http301 = false);
	}

/* =============================================================
	Display
============================================================= */
	protected static function display($data) {
		$jsonm = self::getJsonModule();
		$page  = self::pw('page');
		$page->refreshurl   = self::paymentsUrl($data->vendorID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);

		self::initHooks();
		return self::displayOrders($data);
	}

	private static function displayOrders($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Sales History File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}

		$formatter = self::getFormatter();
		$vendor    = self::getVendor($data->vendorID);
		return $config->twig->render('vendors/vi/payments/display.twig', ['vendor' => $vendor, 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint()]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function paymentsUrl($vendorID, $refreshdata = false) {
		$url = new Purl(self::viPaymentsUrl($vendorID));

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['vendorID|text', 'shipfromID|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['VIPAYMENT', "VENDID=$vars->vendorID"];
		if ($vars->shipfromID) {
			$data[] = "SHIPID=$vars->shipfromID";
		}
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

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMvi');

		$m->addHook('Page(pw_template=vi)::documentListUrl', function($event) {
			$event->return = Documents::documentsUrlPo($event->arguments(0), $event->arguments(1));
		});
	}
}
