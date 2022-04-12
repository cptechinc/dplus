<?php namespace Controllers\Wm\Inventory\Provalley;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
use Processwire\SearchInventory, Processwire\WarehouseManagement,ProcessWire\HtmlWriter;
// Dplus Configs
use Dplus\Configs;
// Dplus Filters
use Dplus\Filters;
// Mvc Controllers
use Controllers\Mwm\Base;

class PrintGs1 extends Base {
	const DPLUSPERMISSION = 'wm';
	const JSONCODE = 'whse-printgs1labelscan';

	private static $jsonm;

/* =============================================================
	Indexes
============================================================= */
	static public function index($data) {
		$fields = ['scan|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->scan) === false) {
			return self::scan($data);
		}
		return self::initScreen($data);
	}

	static public function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text', 'scan|text']);

		switch ($data->action) {
			case 'print-labels':
				$success = self::printLabels($data);
				if ($success === false) {
					self::redirect(self::scanUrl($data->scan), $http301 = false);
				}
				self::redirect(self::scanUrl(), $http301 = false);
				break;
			default:
				self::redirect(self::scanUrl(), $http301 = false);
				break;
		}
	}

	static private function scan($data) {
		self::requestSearch($data);
		$exists = self::verifyData($data);

		if ($exists === false) {
			$html  = self::pw('config')->twig->render('util/bootstrap/alert.twig', ['type' => 'danger', 'headerclass' => 'text-white', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Could not find JSON']);
			$html .= '<div class="mb-3"></div>';
			$html .= self::scanForm($data);
			return $html;
		}
		return self::scanResult($data);
	}

	static private function scanResult($data) {
		$json = self::getJsonModule()->getFile(self::JSONCODE);

		if ($json['error']) {
			$html = '';
			$html .= self::pw('config')->twig->render('util/bootstrap/alert.twig', ['type' => 'danger', 'headerclass' => 'text-white', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['message']]);
			$html .= '<div class="mb-3"></div>';
			$html .= self::scanForm($data);
			return $html;
		}
		self::pw('page')->headline = "Print GS1 Label";
		return self::scanResultDisplay($data, $json);
	}

	static private function printLabels($data) {
		self::sanitizeParametersShort($data, ['scan|text', 'itemID|text', 'lotserial|text', 'lotref|text', 'date|text', 'qty|float', 'labels|int']);
		if (empty($data->itemID) || empty($data->lotref)) {
			// return false;
		}
		self::requestPrintLabels($data);
		self::pw('session')->setFor('print-gs1', 'printed-labels', $data);
		return true;
	}

/* =============================================================
	Data Processing
============================================================= */
	static private function verifyData($data) {
		self::sanitizeParametersShort($data, ['scan|text']);

		$jsonm = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');

		if ($jsonm->exists(self::JSONCODE) === false) {
			$session->setFor(self::JSONCODE, $data->scan, ($session->getFor(self::JSONCODE, $data->scan) + 1));
			if ($session->getFor(self::JSONCODE, $data->scan) > 3) {
				return false;
			}
			$session->redirect(self::scanUrl($data->scan, $refresh = true));
		}

		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['error'] === true) {
				return true;
			}

			if ($json['scan'] != $data->scan) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::scanUrl($data->scan, $refresh = true), $http301 = false);
			}
			$session->setFor(self::JSONCODE, $data->scan, 0);
			return true;
		}

		if ($session->getFor(self::JSONCODE, $data->scan) > 3) {
			return false;
		}
		$session->setFor(self::JSONCODE, $data->scan, ($session->getFor(self::JSONCODE, $data->scan) + 1));
		$session->redirect(self::scanUrl($data->scan, $refresh = true), $http301 = false);
	}


/* =============================================================
	URLs
============================================================= */
	static public function scanUrl($scan = '') {
		$url = new Purl(Inventory::subfunctionUrl('print-gs1'));
		if ($scan) {
			$url->query->set('scan', $scan);
		}
		return $url->getUrl();
	}

	static public function scanChooseItemUrl($scan, $itemID, $vendorID) {
		$url = new Purl(Inventory::subfunctionUrl('print-gs1'));
		$url->query->set('scan', $scan);
		$url->query->set('itemID', $itemID);
		$url->query->set('vendorID', $vendorID);
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	static private function initScreen($data) {
		$html = '';

		if (self::pw('session')->getFor('print-gs1', 'printed-labels')) {
			$whsesession = self::getWhseSession();
			if ($whsesession->hasError()) {
				$html .= self::pw('config')->twig->render('util/bootstrap/alert.twig', ['type' => 'danger', 'headerclass' => 'text-white', 'title' => 'Print Labels Error', 'iconclass' => 'fa fa-print fa-2x', 'message' => $whsesession->status]);
			}

			if ($whsesession->hasError() === false) {
				$lot = self::pw('session')->getFor('print-gs1', 'printed-labels');
				$msg = "Printing Label for $lot->itemID $lot->lotref";
				$html .= self::pw('config')->twig->render('util/bootstrap/alert.twig', ['type' => 'success', 'headerclass' => 'text-white', 'title' => 'Printed Labels', 'iconclass' => 'fa fa-print fa-2x', 'message' => $msg]);
			}
			$html .= '<div class="mb-3"></div>';
			self::pw('session')->removeFor('print-gs1', 'printed-labels');
		}
		$html .= self::scanForm($data);
		return $html;
	}

	static private function scanForm($data) {
		return self::pw('config')->twig->render('mii/loti/forms/scan.twig');
	}

	static private function scanResultDisplay($data, array $json) {
		self::sanitizeParametersShort($data, ['scan|text', 'itemID|text', 'vendorID|text']);
		self::initHooks();

		if (array_key_exists('lots', $json)) {
			return self::lotForm($data, $json);
		}

		if (array_key_exists('items', $json)) {
			return self::scanResultItemsDisplay($data, $json);
		}
		return '';
	}

	static private function scanResultItemsDisplay($data, array $json) {
		if (array_key_exists('items', $json)) {
			if (sizeof($json['items']) == 1) {
				$data->itemID   = $json['items'][0]['itemid'];
				$data->vendorID = $json['items'][0]['vendorid'];
			}

			if (empty($data->itemID) === false) {
				$json['lots'] = ['itemid' => $data->itemID, 'vendorid' => $data->vendorID, 'lotnbr' => '', 'lotref' => '', 'productiondate' => '', 'qty' => ''];
				return self::lotForm($data, $json);
			}
			return $config->twig->render('warehouse/inventory/provalley/print-gs1/results/items.twig', ['json' => $json, 'itm' => self::pw('modules')->get('Itm')]);
		}
		return '';
	}

	static private function lotForm($data, array $json) {
		self::lotFormJs($data);
		return self::pw('config')->twig->render('warehouse/inventory/provalley/print-gs1/results/lots.twig', ['json' => $json, 'itm' => self::pw('modules')->get('Itm')]);
	}

	static private function lotFormJs($data) {
		self::pw('page')->js .= self::pw('config')->twig->render('warehouse/inventory/provalley/print-gs1/forms/.js.twig');
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
	}

/* =============================================================
	Requests
============================================================= */
	static private function requestSearch($data) {
		self::sendRequest(['PRINTGS1LABELSCAN', "QUERY=$data->scan"]);
	}

	static private function requestPrintLabels($data) {
		$date = date('Ymd', strtotime($data->date));
		$vars = [
			'PRINTGS1LABEL',
			"VENDOR=$data->vendorID",
			"ITEMID=$data->itemID",
			"LOTNBR=$data->lotserial",
			"LOTREF=$data->lotref",
			"PRODDATE=$date",
			"QTY=$data->qty",
			"NBRLABELS=$data->labels"
		];
		self::sendRequest($vars);
	}

	static private function sendRequest(array $data, $sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = self::pw('modules')->get('DplusOnlineDatabase')->db_name;
		$data = array_merge(["DBNAME=$db"], $data);
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['warehouse'], $sessionID);
	}

/* =============================================================
	Validator, Module Getters
============================================================= */
	static public function validateUserPermission(User $user = null) {
		if (empty($user)) {
			$user = self::pw('user');
		}
		return $user->has_function(self::DPLUSPERMISSION);
	}

	public static function getJsonModule() {
		if (empty(self::$jsonm)) {
			self::$jsonm = self::pw('modules')->get('JsonDataFilesSession');
		}
		return self::$jsonm;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('WarehouseManagement');

		$m->addHook('Page(pw_template=whse-inv-provalley)::scanChooseItemUrl', function($event) {
			$event->return = self::scanChooseItemUrl($event->arguments(0), $event->arguments(1), $event->arguments(2));
		});
	}
}
