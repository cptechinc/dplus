<?php namespace Controllers\Mii\Ii;
// Purl URI Manipulation Library
use Purl\Url as Purl;

class Costing extends Base {
	const JSONCODE       = 'ii-costing';
	const PERMISSION_IIO = 'costing';

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
			self::requestJson($data->itemID, session_id());
			self::pw('session')->redirect($refreshurl = self::costingUrl($data->itemID), $http301 = false);
		}
		return self::costing($data);
	}

	private static function costing($data) {
		self::getData($data);
		self::pw('page')->headline = "II: $data->itemID Costing";
		return self::displayCosting($data);
	}

/* =============================================================
	Data Requests
============================================================= */
	public static function requestJson($itemID, $sessionID) {
		$sessionID = $sessionID ? $sessionID : session_id();
		$data = array('IICOST', "ITEMID=$itemID");
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	URLs
============================================================= */
	public static function costingUrl($itemID = '', $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('costing');

		if ($itemID) {
			$url->query->set('itemID', $itemID);
			if ($refreshdata) {
				$url->query->set('refresh', 'true');
			}
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
		$session = self::pw('session');

		if ($jsonm->exists(self::JSONCODE)) {
			if (self::jsonItemidMatches($json['itemid'], $data->itemID) === false) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::costingUrl($data->itemID, $refreshdata = true), $http301 = false);
			}
			$session->setFor('ii', 'costing', 0);
			return true;
		}

		if ($session->getFor('ii', 'costing') > 3) {
			return false;
		}
		$session->setFor('ii', 'costing', ($session->getFor('ii', 'costing') + 1));
		$session->redirect(self::costingUrl($data->itemID, $refreshdata = true), $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayCosting($data) {
		$html = '';
		$html .= self::breadCrumbs();
		$html .= self::displayData($data);
		return $html;
	}

	private static function displayData($data) {
		$config = self::pw('config');
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);


		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Costing File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$iim  = self::pw('modules')->get('DpagesMii');
		$page = self::pw('page');
		$page->refreshurl   = self::costingUrl($data->itemID, $refreshdata = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		return $config->twig->render('items/ii/costing/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'module_json' => $jsonm->jsonm]);
	}
}
