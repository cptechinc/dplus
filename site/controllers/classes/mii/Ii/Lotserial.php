<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ii\Lotserial as Formatter;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class Lotserial extends IiFunction {
	const JSONCODE          = 'ii-lotserial';
	const PERMISSION_IIO    = '';

	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data);
			self::pw('session')->redirect(self::lotserialUrl($data->itemID), $http301 = false);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();

		return self::lotserial($data);
	}

	public static function requestJson($vars) {
		$fields = ['itemID|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['IILOTSER', "ITEMID=$vars->itemID"];
		self::sendRequest($data, $vars->sessionID);
	}

	public static function lotserialUrl($itemID, $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('lotserial');
		$url->query->set('itemID', $itemID);

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

	public static function lotserial($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();
		self::sanitizeParametersShort($data, ['itemID|text']);
		$html = '';

		$page    = self::pw('page');
		$config  = self::pw('config');
		$pages   = self::pw('pages');
		$modules = self::pw('modules');
		$htmlwriter = $modules->get('HtmlWriter');
		$jsonM      = $modules->get('JsonDataFiles');

		$page->headline = "II: $data->itemID Lot / Serial";
		$html .= self::breadCrumbs();;
		$html .= self::lotserialData($data);
		return $html;
	}

	private static function lotserialData($data) {
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
				$session->redirect(self::lotserialUrl($data->itemID, $refresh = true), $http301 = false);
			}
			$session->setFor('ii', 'lotserial', 0);
			$refreshurl = self::lotserialUrl($data->itemID, $refresh = true);
			$html .= self::lotserialDisplay($data, $json);
			return $html;
		}

		if ($session->getFor('ii', 'lotserial') > 3) {
			$page->headline = "Lot / Serial File could not be loaded";
			$html .= self::lotserialDisplay($data, $json);
			return $html;
		} else {
			$session->setFor('ii', 'lotserial', ($session->getFor('ii', 'lotserial') + 1));
			$session->redirect(self::lotserialUrl($data->itemID, $refresh = true), $http301 = false);
		}
	}

	protected static function lotserialDisplay($data, $json) {
		$jsonm  = self::getJsonModule();
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Lot / Serial File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl = self::lotserialUrl($data->itemID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$formatter = new Formatter();
		$formatter->init_formatter();
		$html =  '';
		$html .= $config->twig->render('items/ii/lotserial/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'module_json' => $jsonm->jsonm]);
		return $html;
	}
}
