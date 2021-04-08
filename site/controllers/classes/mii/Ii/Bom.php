<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class Bom extends IiFunction {
	const JSONCODE          = 'ii-components-bom';
	const PERMISSION_IIO    = 'bom';
	const DATE_FORMAT       = 'm/d/Y';
	const DATE_FORMAT_DPLUS = 'Ymd';

	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool', 'qty|int', 'type|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data, session_id());
			self::pw('session')->redirect(self::bomUrl($data->itemID, $data->qty, $data->type), $http301 = false);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();

		if (empty($data->qty) === false || empty($data->type) === false) {
			return self::bom($data);
		}
		return self::qtyForm($data);
	}

	public static function requestJson($vars) {
		$fields = ['itemID|text', 'qty|int', 'sessionID|text', 'type|text'];
		$vars   = self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$type = $data->type == 'consolidated' ? 'IIBOMCONS' : 'IIBOMSINGLE';
		$data = [$type,"ITEMID=$vars->itemID","QTYNEEDED=$vars->qty"];
		self::sendRequest($data, $vars->sessionID);
	}

	public static function bomUrl($itemID, $qty = 0, $type = '', $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('bom');
		$url->query->set('itemID', $itemID);

		if ($qty) {
			$url->query->set('qty', $qty);

			if ($type) {
				$url->query->set('type', $type);

				if ($refreshdata) {
					$url->query->set('refresh', 'true');
				}
			}
		}
		return $url->getUrl();
	}

	public static function bom($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();
		$data = self::sanitizeParametersShort($data, ['itemID|text', 'qty|int', 'type|text']);
		$html = '';

		$page    = self::pw('page');
		$config  = self::pw('config');
		$pages   = self::pw('pages');
		$modules = self::pw('modules');
		$htmlwriter = $modules->get('HtmlWriter');
		$jsonM      = $modules->get('JsonDataFiles');

		$page->headline = "II: $data->itemID BoM";
		$html .= self::breadCrumbs();;
		$html .= self::bomData($data);
		return $html;
	}

	private static function bomData($data) {
		$data    = self::sanitizeParametersShort($data, ['itemID|text', 'qty|int', 'type|text']);
		$jsonm   = self::getJsonModule();
		$jsoncode = self::JSONCODE . "-$data->type";
		$json    = $jsonm->getFile($jsoncode);
		$page    = self::pw('page');
		$config  = self::pw('config');
		$session = self::pw('session');
		$html = '';

		if ($jsonm->exists($jsoncode)) {
			if ($json['itemid'] != $data->itemID) {
				$jsonm->delete($jsoncode);
				$session->redirect(self::bomUrl($data->itemID, $data->qty, $data->type, $refresh = true), $http301 = false);
			}
			$session->setFor('ii', 'bom', 0);
			$refreshurl = self::bomUrl($data->itemID, $data->qty, $data->type, $refresh = true);
			$html .= self::bomDataDisplay($data, $json);
			return $html;
		}

		if ($session->getFor('ii', 'bom') > 3) {
			$page->headline = "BoM File could not be loaded";
			$html .= self::bomDataDisplay($data, $json);
			return $html;
		} else {
			$session->setFor('ii', 'bom', ($session->getFor('ii', 'bom') + 1));
			$session->redirect(self::bomUrl($data->itemID, $data->qty, $data->type, $refresh = true), $http301 = false);
		}
	}

	protected static function bomDataDisplay($data, $json) {
		$jsonm  = self::getJsonModule();
		$config = self::pw('config');
		$jsoncode = self::JSONCODE . "-$data->type";

		if ($jsonm->exists($jsoncode) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'BoM File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl = self::bomUrl($data->itemID, $data->qty, $data->type, $refresh = true);
		$page->lastmodified = $jsonm->lastModified($jsoncode);
		$html =  '';
		$html .= $config->twig->render('items/ii/components/bom/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'module_json' => $jsonm->jsonm, 'type' => $data->type]);
		return $html;
	}

	private static function qtyForm($data) {
		$data = self::sanitizeParametersShort($data, ['itemID|text']);
		$config = self::pw('config');
		$page = self::pw('page');

		$page->headline = "II: $data->itemID BoM";
		$html = self::breadCrumbs();
		$html .= $config->twig->render('items/ii/components/bom/qty-form.twig', ['itemID' => $data->itemID]);
		$config->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		return $html;
	}

}
