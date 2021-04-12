<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class Stock extends IiFunction {
	const JSONCODE       = 'ii-stock_whse';
	const PERMISSION_IIO = 'stock';

/* =============================================================
	1. Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data);
			self::pw('session')->redirect(self::stockUrl($data->itemID), $http301 = false);
		}
		return self::stock($data);
	}

	public static function stock($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::sanitizeParametersShort($data, ['itemID|text']);

		self::getData($data);
		$page    = self::pw('page');
		$page->headline = "$data->itemID Stock";
		$html = '';
		$html .= self::breadCrumbs();
		$html .= self::display($data);
		return $html;
	}

/* =============================================================
	2. Data Requests
============================================================= */
	public static function requestJson($vars) {
		self::sanitizeParametersShort($vars, ['itemID|text','sessionID|text']);
		$data = ['IISTKBYWHSE', "ITEMID=$vars->itemID"];
		self::sendRequest($data);
	}

/* =============================================================
	3. URLs
============================================================= */
	public static function stockUrl($itemID = '', $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('stock');

		if ($itemID) {
			$url->query->set('itemID', $itemID);
			if ($refreshdata) {
				$url->query->set('refresh', 'true');
			}
		}
		return $url->getUrl();
	}

/* =============================================================
	4. Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['itemID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');

		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['itemid'] != $data->itemID) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::stockUrl($data->itemID, $refreshdata = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ii', 'stock') > 3) {
			return false;
		}
		$session->setFor('ii', 'stock', ($session->getFor('ii', 'stock') + 1));
		$session->redirect(self::stockUrl($data->itemID, $refreshdata = true), $http301 = false);
	}

/* =============================================================
	5. Displays
============================================================= */
	protected static function display($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Stock File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$iim  = self::pw('modules')->get('DpagesMii');
		$page = self::pw('page');
		$page->refreshurl = self::stockUrl($data->itemID, $refreshdata = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		return $config->twig->render('items/ii/stock-whse/display.twig', ['item' => self::getItmItem($data->itemID), 'module_ii' => $iim, 'company' => $config->company, 'json' => $json, 'module_json' => $jsonm->jsonm]);
	}

}
