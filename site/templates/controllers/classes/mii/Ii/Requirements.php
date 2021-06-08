<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// Dplus Model
use WarehouseQuery, Warehouse;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class Requirements extends IiFunction {
	const JSONCODE       = 'ii-requirements';
	const PERMISSION_IIO = 'requirements';

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
			self::requestJson($data, session_id());
			self::pw('session')->redirect($refreshurl = self::requirementsUrl($data->itemID), $http301 = false);
		}
		return self::requirements($data);
	}

	public static function requirements($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::sanitizeParametersShort($data, ['itemID|text']);

		self::getData($data);
		$page    = self::pw('page');
		$page->headline = "$data->itemID Requirements";
		$html = '';
		$html .= self::breadCrumbs();
		$html .= self::display($data);
		return $html;
	}

/* =============================================================
	2. Data Requests
============================================================= */
	public static function requestJson($vars) {
		$fields = ['itemID|text', 'whseID|text', 'view|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();

		if (empty($view)) {
			$user = self::pw('user');
			$iio  = self::getIio();
			$options = $iio->useriio($user->loginid);
			$vars->view = $options::VIEW_REQUIREMENTS_OPTIONS_JSON[$options->view_requirements];
		}

		$data = ['IIREQUIRE', "ITEMID=$vars->itemID", "WHSE=$vars->whseID", "REQAVL=$vars->view"];
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	3. URLs
============================================================= */
	public static function requirementsUrl($itemID = '', $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('requirements');

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
				$session->redirect(self::requirementsUrl($data->itemID, $refreshdata = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ii', 'requirements') > 3) {
			return false;
		}
		$session->setFor('ii', 'requirements', ($session->getFor('ii', 'requirements') + 1));
		$session->redirect(self::requirementsUrl($data->itemID, $refresh= true), $http301 = false);
	}

	protected static function display($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Requirements File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$iim = self::pw('modules')->get('DpagesMii');
		$warehouses = WarehouseQuery::create()->find();

		$page->refreshurl = self::requirementsUrl($data->itemID, $refreshdata = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$html = $config->twig->render('items/ii/requirements/display.twig', ['item' => self::getItmItem($data->itemID), 'module_ii' => $iim, 'warehouses' => $warehouses, 'json' => $json, 'module_json' => $jsonm->jsonm]);
		return $html;
	}
}
