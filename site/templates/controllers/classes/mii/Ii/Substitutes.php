<?php namespace Controllers\Mii\Ii;
// Purl URI Manipulation Library
use Purl\Url as Purl;

class Substitutes extends Base {
	const JSONCODE       = 'ii-substitutes';
	const PERMISSION_IIO = 'substitutes';

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
			self::requestJson($data);
			self::pw('session')->redirect($refreshurl = self::substitutesUrl($data->itemID), $http301 = false);
		}
		return self::substitutes($data);
	}

	private static function substitutes($data) {
		self::getData($data);
		self::pw('page')->headline = "$data->itemID Costing";
		return self::displaySubtitutes($data);
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		self::sanitizeParametersShort($vars, ['itemID|text', 'sessionID|text']);
		$vars->sessionID = $vars->sessionID ? $vars->sessionID : session_id();
		$data = array('IISUB', "ITEMID=$vars->itemID");
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	URLs
============================================================= */
	public static function substitutesUrl($itemID = '', $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('substitutes');

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
				$session->redirect(self::substitutesUrl($data->itemID, $refreshdata = true), $http301 = false);
			}
			$session->setFor('ii', 'substitutes', 0);
			return true;
		}

		if ($session->getFor('ii', 'substitutes') > 3) {
			return false;
		}
		$session->setFor('ii', 'substitutes', ($session->getFor('ii', 'substitutes') + 1));
		$session->redirect(self::substitutesUrl($data->itemID, $refreshdata = true), $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displaySubtitutes($data) {
		$html = '';
		$html .= self::breadCrumbs();
		$html .= self::displayData($data);
		return $html;
	}

	protected static function displayData($data) {
		$config = self::pw('config');
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Substitutes File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl   = self::substitutesUrl($data->itemID, $refreshdata = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		return $config->twig->render('items/ii/substitutes/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'module_json' => $jsonm->jsonm]);
	}
}
