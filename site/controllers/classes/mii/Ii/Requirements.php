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
	const JSONCODE = 'ii-requirements';

	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data, session_id());
			self::pw('session')->redirect($refreshurl = self::requirementsUrl($data->itemID), $http301 = false);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();
		return self::requirements($data);
	}

	public static function requestJson($vars) {
		$fields = ['itemID|text', 'whseID|text', 'view|text', 'sessionID|text'];
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		if (empty($view)) {
			$user = self::pw('user');
			$iio  = self::getIio();
			$options = $iio->useriio($user->loginid);
			$vars->view = $options::VIEW_REQUIREMENTS_OPTIONS_JSON[$options->view_requirements];
		}

		$db = self::pw('modules')->get('DplusOnlineDatabase')->db_name;
		$data = ["DBNAME=$db", 'IIREQUIRE', "ITEMID=$vars->itemID", "WHSE=$vars->whseID", "REQAVL=$vars->view"];
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $vars->sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['default'], $vars->sessionID);
	}

	public static function requirementsUrl($itemID = '', $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('requirements');

		$url->query->set('itemID', $itemID);
		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

	public static function requirements($data) {
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

		$page->headline = "$data->itemID Requirements";
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
				$session->redirect(self::requirementsUrl($data->itemID, $refreshdata = true), $http301 = false);
			}
			$session->setFor('ii', 'requirements', 0);
			$html .= self::requirementsDataDisplay($data, $json);
			return $html;
		}

		if ($session->getFor('ii', 'requirements') > 3) {
			$page->headline = "Requirements File could not be loaded";
			$html .= self::requirementsDataDisplay($data, $json);
			return $html;
		} else {
			$session->setFor('ii', 'requirements', ($session->getFor('ii', 'requirements') + 1));
			$session->redirect(self::requirementsUrl($data->itemID, $refresh= true), $http301 = false);
		}
	}

	protected static function requirementsDataDisplay($data, $json) {
		$jsonm  = self::getJsonModule();
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
