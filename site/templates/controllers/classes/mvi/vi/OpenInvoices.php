<?php namespace Controllers\Mvi\Vi;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Vi\OpenInvoices as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;

class OpenInvoices extends Subfunction {
	const PERMISSION_VIO = '';
	const JSONCODE       = 'vi-openinvoices';

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
			self::pw('session')->redirect(self::invoicesUrl($data->vendorID), $http301 = false);
		}
		return self::invoices($data);
	}

	private static function invoices($data) {
		self::getData($data);
		self::pw('page')->headline = "VI: $data->vendorID Open Invoices";

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

		if ($session->getFor('vi', 'open-invoices') > 3 && $json['vendid'] == $data->vendorID) {
			return false;
		}
		$session->setFor('vi', 'open-invoices', ($session->getFor('vi', 'open-invoices') + 1));
		$session->redirect(self::invoicesUrl($data->vendorID, $refresh = true), $http301 = false);
	}

/* =============================================================
	Display
============================================================= */
	protected static function display($data) {
		$jsonm = self::getJsonModule();
		$page  = self::pw('page');
		$page->refreshurl   = self::invoicesUrl($data->vendorID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);

		self::initHooks();
		return self::displayInvoices($data);
	}

	private static function displayInvoices($data) {
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
		return $config->twig->render('vendors/vi/open-invoices/display.twig', ['vendor' => $vendor, 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function invoicesUrl($vendorID, $refreshdata = false) {
		$url = new Purl(self::viOpenInvoicesUrl($vendorID));

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
		$data = ['VIOPENINV', "VENDID=$vars->vendorID"];
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
