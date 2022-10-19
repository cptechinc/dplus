<?php namespace Controllers\Mvi\Vi;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Vi\PurchaseHistory as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;

class PurchaseHistory extends Subfunction {
	const PERMISSION_VIO = 'purchasehistory';
	const JSONCODE       = 'vi-purchase-history';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['vendorID|string', 'date|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateVendorid($data->vendorID) === false) {
			self::pw('session')->redirect(self::viUrl(), $http301 = false);
		}

		if (self::validateVendoridPermission($data) === false) {
			return self::displayInvalidVendorOrPermissions($data);
		}

		if ($data->refresh && $data->date) {
			self::requestJson($data);
			self::pw('session')->redirect(self::historyUrl($data->vendorID, $data->date), $http301 = false);
		}

		if (empty($data->date)) {
			return self::initial($data);
		}
		return self::history($data);
	}

	private static function history($data) {
		self::getData($data);
		self::pw('page')->headline = "VI: $data->vendorID Purchase History";

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::display($data);
		return $html;
	}

	private static function initial($data) {
		self::pw('page')->headline = "VI: $data->vendorID Purchase History";

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::displayInitial($data);
		return $html;
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['vendorID|string', 'date|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');

		$date = date('Ymd', strtotime($data->date));

		if ($session->getFor('vi', 'purchase-history') > 3 && $json['vendid'] == $data->vendorID && $json['date'] == $date) {
			return false;
		}
		$session->setFor('vi', 'purchase-history', ($session->getFor('vi', 'purchase-history') + 1));
		$session->redirect(self::historyUrl($data->vendorID, $data->date, $refresh = true), $http301 = false);
	}

/* =============================================================
	Display
============================================================= */
	private static function displayInitial($data) {
		return self::pw('config')->twig->render('vendors/vi/purchase-history/initial/display.twig', []);
	}

	private static function display($data) {
		$jsonm = self::getJsonModule();
		$page  = self::pw('page');
		$page->refreshurl   = self::historyUrl($data->vendorID, $data->date, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);

		self::initHooks();
		return self::displayHistory($data);
	}

	private static function displayHistory($data) {
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
		$docm      = self::getDocFinder();
		$vendor    = self::getVendor($data->vendorID);
		return $config->twig->render('vendors/vi/purchase-history/display.twig', ['vendor' => $vendor, 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function historyUrl($vendorID, $date = '', $refreshdata = false) {
		$url = new Purl(self::viPurchaseHistoryUrl($vendorID));

		if ($date) {
			$url->query->set('date', $date);
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
		$fields = ['vendorID|string', 'shipfromID|text', 'date|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['VIPURCHHIST', "VENDID=$vars->vendorID"];
		if ($vars->shipfromID) {
			$data[] = "SHIPID=$vars->shipfromID";
		}
		$date = date('Ymd', strtotime($vars->date));
		$data[] = "DATE=$date";
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
		return new DocFinders\ApInvoice();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMvi');

		$m->addHook('Page(pw_template=vi)::documentListUrl', function($event) {
			$event->return = Documents::documentsUrlAp($event->arguments(0), $event->arguments(1));
		});
	}
}
