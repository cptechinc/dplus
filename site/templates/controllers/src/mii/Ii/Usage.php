<?php namespace Controllers\Mii\Ii;
// Purl URI Manipulation Library
use Purl\Url as Purl;

class Usage extends Base {
	const JSONCODE       = 'ii-usage';
	const PERMISSION_IIO = 'usage';

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
			self::pw('session')->redirect($refreshurl = self::usageUrl($data->itemID), $http301 = false);
		}
		return self::usage($data);
	}

	private static function usage($data) {
		self::getData($data);
		self::pw('page')->headline = "$data->itemID Usage";
		return self::displayUsage($data);
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['itemID|text', 'whseID|text', 'view|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['IIUSAGE', "ITEMID=$vars->itemID"];
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	URLs
============================================================= */
	public static function usageUrl($itemID = '', $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('usage');

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
		$session = self::pw('session');;

		if ($jsonm->exists(self::JSONCODE)) {
			if (self::jsonItemidMatches($json['itemid'], $data->itemID) === false) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::usageUrl($data->itemID, $refreshdata = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ii', 'usage') > 3) {
			return false;
		}
		$session->setFor('ii', 'usage', ($session->getFor('ii', 'usage') + 1));
		$session->redirect(self::usageUrl($data->itemID, $refreshdata = true), $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayUsage($data) {
		$html = '';
		$html .= self::breadCrumbs();
		$html .= self::displayData($data);
		return $html;
	}

	private static function displayData($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Usage File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$usagem = self::pw('modules')->get('IiUsage');
		$page   = self::pw('page');
		$page->refreshurl = self::usageUrl($data->itemID, $refreshdata = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);

		$config->styles->append('//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css');
		$config->scripts->append('//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js');
		$config->scripts->append('//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js');
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/moment.js'));

		$page->js    = $config->twig->render('items/ii/usage/warehouses.js.twig', ['json' => $json, 'module_json' => $jsonm->jsonm, 'module_usage' => $usagem]);
		return $config->twig->render('items/ii/usage/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'module_json' => $jsonm->jsonm]);
	}
}
