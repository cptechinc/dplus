<?php namespace Controllers\Mvi\Vi;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;

class Summary extends Subfunction {
	const PERMISSION_VIO = 'summary';
	const JSONCODE       = 'vi-24monthsummary';

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
			self::pw('session')->redirect(self::summaryUrl($data->vendorID), $http301 = false);
		}
		return self::summary($data);
	}

	private static function summary($data) {
		self::getData($data);
		$config = self::pw('config');
		$page   = self::pw('page');
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);

		$page->headline = "VI: $data->vendorID Summary";
		$page->js .= $config->twig->render('vendors/vi/summary/summary.js.twig', ['json' => $json]);
		$fh = self::getFileHasher();
		$config->styles->append($fh->getHashUrl('styles/lib/morris.css'));
		$config->scripts->append($fh->getHashUrl('scripts/lib/raphael.js'));
		$config->scripts->append($fh->getHashUrl('scripts/lib/morris.js'));

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

		if ($session->getFor('vi', 'summary') > 3 && $json['vendid'] == $data->vendorID) {
			return false;
		}
		$session->setFor('vi', 'summary', ($session->getFor('vi', 'summary') + 1));
		$session->redirect(self::summaryUrl($data->vendorID, $refresh = true), $http301 = false);
	}

/* =============================================================
	Display
============================================================= */
	protected static function display($data) {
		$jsonm = self::getJsonModule();
		$page  = self::pw('page');
		$page->refreshurl   = self::summaryUrl($data->vendorID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);

		self::initHooks();
		return self::displaySummary($data);
	}

	private static function displaySummary($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Sales History File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}

		$vendor    = self::getVendor($data->vendorID);
		return $config->twig->render('vendors/vi/summary/display.twig', ['vendor' => $vendor, 'json' => $json]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function summaryUrl($vendorID, $refreshdata = false) {
		$url = new Purl(self::viSummaryUrl($vendorID));

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
		$data = ['VIMONTHSUM', "VENDID=$vars->vendorID"];
		if ($vars->shipfromID) {
			$data[] = "SHIPID=$vars->shipfromID";
		}
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	Supplemental
============================================================= */

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMvi');

	}
}
