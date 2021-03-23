<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class Stock extends IiFunction {
	const JSONCODE = 'ii-stock_whse';

	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestStockJson($data->itemID, session_id());
			self::pw('session')->redirect($refreshurl = self::stockUrl($data->itemID), $http301 = false);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();
		return self::stock($data);
	}

	public static function requestStockJson($itemID, $sessionID) {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = self::pw('modules')->get('DplusOnlineDatabase')->db_name;
		$data = array("DBNAME=$db", 'IISTKBYWHSE', "ITEMID=$itemID");
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['default'], $sessionID);
	}

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

	public static function stock($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();
		$data = self::sanitizeParametersShort($data, ['itemID|text']);
		$html = '';

		$page    = self::pw('page');
		$config  = self::pw('config');
		$pages   = self::pw('pages');
		$modules = self::pw('modules');
		$htmlwriter = $modules->get('HtmlWriter');
		$jsonM      = $modules->get('JsonDataFiles');

		$page->headline = "$data->itemID Stock";
		$html .= self::stockData($data);
		return $html;
	}

	private static function stockData($data) {
		$data    = self::sanitizeParametersShort($data, ['itemID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$page    = self::pw('page');
		$config  = self::pw('config');
		$session = self::pw('session');
		$html = '';

		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['itemid'] != $data->itemID) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::stockUrl($data->itemID, $refreshdata = true), $http301 = false);
			}
			$session->setFor('ii', 'stock', 0);
			$refreshurl = self::stockUrl($data->itemID, $refreshdata = true);
			$html .= self::stockDataDisplay($data, $json);
			return $html;
		}

		if ($session->getFor('ii', 'stock') > 3) {
			$page->headline = "Stock File could not be loaded";
			$html .= self::stockDataDisplay($data, $json);
			return $html;
		} else {
			$session->setFor('ii', 'stock', ($session->getFor('ii', 'stock') + 1));
			$session->redirect($page->get_itemstockURL($data->itemID), $http301 = false);
		}
	}

	protected static function stockDataDisplay($data, $json) {
		$jsonm  = self::getJsonModule();
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
		return $config->twig->render('items/ii/stock-whse/display.twig', ['item' => self::getItmItem($data->itemID), 'module_ii' => $iim, 'company' => $config->companys, 'json' => $json, 'module_json' => $jsonm->jsonm]);
	}

}
