<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class Usage extends IiFunction {
	const JSONCODE       = 'ii-usage';
	const PERMISSION_IIO = 'usage';

	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data);
			self::pw('session')->redirect($refreshurl = self::usageUrl($data->itemID), $http301 = false);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();
		return self::usage($data);
	}

	public static function requestJson($vars) {
		$fields = ['itemID|text', 'whseID|text', 'view|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = array('IIUSAGE', "ITEMID=$itemID");
		self::sendRequest($data, $vars->sessionID);
	}

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

	public static function usage($data) {
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

		$page->headline = "$data->itemID Usage";
		$html .= self::breadCrumbs();
		$html .= self::usageData($data);
		return $html;
	}

	private static function usageData($data) {
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
				$session->redirect(self::usageUrl($data->itemID, $refreshdata = true), $http301 = false);
			}
			$session->setFor('ii', 'usage', 0);
			$html .= self::usageDataDisplay($data, $json);
			return $html;
		}

		if ($session->getFor('ii', 'usage') > 3) {
			$page->headline = "Usage File could not be loaded";
			$html .= self::usageDataDisplay($data, $json);
			return $html;
		} else {
			$session->setFor('ii', 'usage', ($session->getFor('ii', 'usage') + 1));
			$session->redirect(self::usageUrl($data->itemID, $refreshdata = true), $http301 = false);
		}
	}

	protected static function usageDataDisplay($data, $json) {
		$jsonm  = self::getJsonModule();
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Usage File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$usagem = self::pw('modules')->get('IiUsage');
		$page = self::pw('page');
		$page->refreshurl = self::usageUrl($data->itemID, $refreshdata = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);

		$config->styles->append('//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css');
		$config->scripts->append('//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js');
		$config->scripts->append('//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js');
		$config->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/lib/moment.js'));

		$html = '';
		$html .= $config->twig->render('items/ii/usage/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'module_json' => $jsonm->jsonm]);
		$page->js    = $config->twig->render('items/ii/usage/warehouses.js.twig', ['json' => $json, 'module_json' => $jsonm->jsonm, 'module_usage' => $usagem]);
		return $html;
	}
}
