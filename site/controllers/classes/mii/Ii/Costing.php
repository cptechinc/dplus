<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class Costing extends IiFunction {
	const JSONCODE       = 'ii-costing';
	const PERMISSION_IIO = 'costing';

	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data->itemID, session_id());
			self::pw('session')->redirect($refreshurl = self::costingUrl($data->itemID), $http301 = false);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();
		return self::costing($data);
	}

	public static function requestJson($itemID, $sessionID) {
		$sessionID = $sessionID ? $sessionID : session_id();
		$data = array('IICOST', "ITEMID=$itemID");
		self::sendRequest($data, $vars->sessionID);
	}

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

	public static function costing($data) {
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

		$page->headline = "$data->itemID Costing";
		$html .= self::breadCrumbs();
		$html .= self::costingData($data);
		return $html;
	}

	private static function costingData($data) {
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
				$session->redirect(self::costingUrl($data->itemID, $refreshdata = true), $http301 = false);
			}
			$session->setFor('ii', 'costing', 0);
			$refreshurl = self::costingUrl($data->itemID, $refreshdata = true);
			$html .= self::costingDataDisplay($data, $json);
			return $html;
		}

		if ($session->getFor('ii', 'costing') > 3) {
			$page->headline = "Costing File could not be loaded";
			$html .= self::costingDataDisplay($data, $json);
			return $html;
		} else {
			$session->setFor('ii', 'costing', ($session->getFor('ii', 'costing') + 1));
			$session->redirect(self::costingUrl($data->itemID, $refreshdata = true), $http301 = false);
		}
	}

	protected static function costingDataDisplay($data, $json) {
		$jsonm  = self::getJsonModule();
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Costing File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$iim  = self::pw('modules')->get('DpagesMii');
		$page = self::pw('page');
		$page->refreshurl = self::costingUrl($data->itemID, $refreshdata = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$html = '';
		$html .= $config->twig->render('items/ii/costing/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'module_json' => $jsonm->jsonm]);
		return $html;
	}
}
