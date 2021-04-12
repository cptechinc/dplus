<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// ProcessWire Classes
use ProcessWire\WireData;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class General extends IiFunction {
	const JSONCODE          = '';
	const JSONCODE_MISC     = 'ii-misc';
	const JSONCODE_NOTES    = 'ii-notes';
	const JSONCODE_USAGE    = 'ii-usage';
	const PERMISSION_IIO    = '';

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
			self::pw('session')->redirect(self::generalUrl($data->itemID), $http301 = false);
		}
		return self::general($data);
	}

	public static function general($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::sanitizeParametersShort($data, ['itemID|text']);
		self::getData($data);
		$page = self::pw('page');
		$page->headline = "II: $data->itemID General";
		$html = '';
		$html .= self::breadCrumbs();
		$html .= self::display($data);
		return $html;
	}

/* =============================================================
	2. Data Requests
============================================================= */
	public static function requestJson($vars) {
		$fields = ['itemID|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['IIGENERAL', "ITEMID=$vars->itemID"];
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	3. URLs
============================================================= */
	public static function generalUrl($itemID, $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('general');
		$url->query->set('itemID', $itemID);

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	4. Data Retrieval
============================================================= */
	private static function getData($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		self::getDataSection($data, self::JSONCODE_MISC);
		self::getDataSection($data, self::JSONCODE_NOTES);
		self::getDataSection($data, self::JSONCODE_USAGE);
	}

	private static function getDataSection($data, $jsoncode) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile($jsoncode);
		$session = self::pw('session');

		if ($jsonm->exists($jsoncode)) {
			if ($json['itemid'] != $data->itemID) {
				$jsonm->delete($jsoncode);
				$session->redirect(self::generalUrl($data->itemID, $refresh = true), $http301 = false);
			}
			$session->setFor('ii', 'general', 0);
			return true;
		}

		if ($session->getFor('ii', 'general') > 3) {
			return false;
		}
		$session->setFor('ii', 'general', ($session->getFor('ii', 'general') + 1));
		$session->redirect(self::generalUrl($data->itemID, $refresh = true), $http301 = false);
	}

/* =============================================================
	5. Displays
============================================================= */
	private static function display($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$html = new WireData();
		$html->misc  = self::displaySection($data, self::JSONCODE_MISC);
		$html->notes = self::displaySection($data, self::JSONCODE_NOTES);
		$html->usage = self::usageDisplay($data);
		return self::pw('config')->twig->render('items/ii/general/display.twig', ['item' => self::getItmItem($data->itemID), 'html' => $html]);
	}

	protected static function displaySection($data, $jsoncode) {
		$code    = str_replace('ii-', '', $jsoncode);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile($jsoncode);
		$config  = self::pw('config');

		if ($jsonm->exists($jsoncode) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => '$code File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl = self::generalUrl($data->itemID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified($jsoncode);
		return $config->twig->render("items/ii/general/$code.twig", ['json' => $json, 'module_json' => $jsonm->jsonm]);
	}

	protected static function usageDisplay($data) {
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(Usage::JSONCODE);
		$config  = self::pw('config');

		if ($jsonm->exists(Usage::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Usage File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$usagem = self::pw('modules')->get('IiUsage');
		$page   = self::pw('page');
		$page->refreshurl   = self::generalUrl($data->itemID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(Usage::JSONCODE);
		$config->styles->append('//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css');
		$config->scripts->append('//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js');
		$config->scripts->append('//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js');
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/moment.js'));
		$page->js .= $config->twig->render('items/ii/usage/warehouses.js.twig', ['json' => $json, 'module_json' => $jsonm->jsonm, 'module_usage' => $usagem]);
		return $config->twig->render('items/ii/general/usage.twig', ['json' => $json, 'module_json' => $jsonm->jsonm]);
	}
}
