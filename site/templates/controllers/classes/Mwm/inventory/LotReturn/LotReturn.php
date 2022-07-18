<?php namespace Controllers\Wm\Inventory;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// Dplus Model
use SalesOrderQuery, SalesOrder;
use PurchaseOrderQuery, PurchaseOrder;
use ItemMasterItemQuery, ItemMasterItem;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
use Processwire\SearchInventory, Processwire\WarehouseManagement,ProcessWire\HtmlWriter;
// Dplus Configs
use Dplus\Configs;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Dplus Databases
use Dplus\Databases\Connectors\Dpluso as DbDpluso;
// Dplus Filters
use Dplus\Filters;
// Mvc Controllers
use Controllers\Wm\Base;

class LotReturn extends Base {
	const DPLUSPERMISSION = 'wm';
	const JSONCODE = 'whse-lotreturn';

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
			case 'search-inventory':
				self::redirect(self::scanUrl($data->scan), $http301 = false);
				break;
			case 'return-lot':
				$success = self::returnLot($data);
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
		self::pw('page')->headline = "Lot Return: " . $json['item']['lotref'];
		self::pw('page')->js .= self::pw('config')->twig->render('warehouse/inventory/lot-return/scanned-lot/js.twig');
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		return self::scannedLotDisplay($data, $json);
	}

	static private function getLotData(array $json) {
		$lotdata = new WireData();
		$lotdata->so   = SalesOrderQuery::create()->findOneByOrdernumber($json['item']['salesorder']['ordernumber']);
		$lotdata->po   = PurchaseOrderQuery::create()->findOneByPonbr($json['item']['purchaseorder']['ponbr']);
		$lotdata->item = ItemMasterItemQuery::create()->findOneByItemid($json['item']['itemid']);
		return $lotdata;
	}

	static private function returnLot($data) {
		self::sanitizeParametersShort($data, ['itemID|text', 'qty|float', 'lotnbr|text', 'lotref|text', 'productiondate|text', 'ordn|text', 'ponbr|text', 'whseID|text', 'binID|text', 'restock|bool']);
		if (empty($data->lotnbr)) {
			return false;
		}

		if ($data->restock === true) {
			$filter = new Filters\Min\WarehouseBin();
			if ($filter->exists($data->whseID, $data->binID) === false) {
				return false;
			}
			self::pw('session')->set('lotreturn-binid', $data->binID);
		}

		self::requestLotReturn($data);
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
			$session->setFor('whse-lotreturn', $data->scan, ($session->getFor('whse-lotreturn', $data->scan) + 1));
			if ($session->getFor('whse-lotreturn', $data->scan) > 3) {
				return false;
			}
			$session->redirect(self::scanUrl($data->scan, $refresh = true));
		}

		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['scan'] != $data->scan) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::scanUrl($data->scan, $refresh = true), $http301 = false);
			}
			$session->setFor('whse-lotreturn', $data->scan, 0);
			return true;
		}

		if ($session->getFor('whse-lotreturn', $data->scan) > 3) {
			return false;
		}
		$session->setFor('whse-lotreturn', $data->scan, ($session->getFor('whse-lotreturn', $data->scan) + 1));
		$session->redirect(self::scanUrl($data->scan, $refresh = true), $http301 = false);
	}

/* =============================================================
	URLs
============================================================= */
	static public function scanUrl($scan = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=whse-lot-return')->url);
		if ($scan) {
			$url->query->set('scan', $scan);
		}
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	static private function scanForm($data) {
		return self::pw('config')->twig->render('mii/loti/forms/scan.twig');
	}

	static private function initScreen($data) {
		$html = '';
		$binID = self::pw('session')->get('lotreturn-binid');
		if (empty($binID) === false) {
			$s = self::getWhseSession();
			if ($s->hasError()) {
				$html .= self::pw('config')->twig->render('util/bootstrap/alert.twig', ['type' => 'danger', 'headerclass' => 'text-white', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $s->status]);
				$html .= '<div class="mb-3"></div>';
			}
		}
		$html .= self::scanForm($data);
		return $html;
	}

	static private function scannedLotDisplay($data, array $json) {
		$config  = self::pw('config');

		$html  = '';
		$html .= $config->twig->render('warehouse/inventory/lot-return/scanned-lot.twig', ['json' => $json]);
		return $html;
	}

/* =============================================================
	Requests
============================================================= */
	static private function requestSearch($data) {
		self::sendRequest(['LOTRETURN', "QUERY=$data->scan"]);
	}

	static private function requestLotReturn($data) {
		$restock = $data->restock ? 'Y' : 'N';
		$date = $data->productiondate ? date('Ymd', strtotime($data->productiondate)) : '';
		$vars = [
			'LOTRETACTION',
			"ITEMID=$data->itemID", "LOTNBR=$data->lotnbr", "LOTREF=$data->lotref", "QTY=$data->qty",
			"PRODDATE=$date", "RESTOCK=$restock",
			"SALESORDER=$data->ordn", "PURCHORDER=$data->ponbr"
		];
		if ($data->restock === true) {
			$vars[] = "BINID=$data->binID";
		}
		self::sendRequest($vars);
	}

	static private function sendRequest(array $data, $sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = DbDpluso::instance()->dbconfig->dbName;
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

	}
}
