<?php namespace Controllers\Mii\Ii;
// Purl URI Manipulation Library
use Purl\Url as Purl;

class Bom extends Base {
	const JSONCODE          = 'ii-components-bom';
	const PERMISSION_IIO    = 'bom';
	const DATE_FORMAT       = 'm/d/Y';
	const DATE_FORMAT_DPLUS = 'Ymd';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool', 'qty|int', 'type|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data, session_id());
			self::pw('session')->redirect(self::bomUrl($data->itemID, $data->qty, $data->type), $http301 = false);
		}

		if (empty($data->qty) === false || empty($data->type) === false) {
			return self::bom($data);
		}
		return self::qtyForm($data);
	}

	private static function bom($data) {
		self::getData($data);
		self::pw('page')->headline = "II: $data->itemID BoM";
		return self::displayBom($data);
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['itemID|text', 'qty|int', 'sessionID|text', 'type|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$type = $data->type == 'consolidated' ? 'IIBOMCONS' : 'IIBOMSINGLE';
		$data = [$type, "ITEMID=$vars->itemID","QTYNEEDED=$vars->qty"];
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	URLs
============================================================= */
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

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		self::sanitizeParametersShort($data, ['itemID|text', 'qty|int', 'type|text']);
		$jsonm    = self::getJsonModule();
		$jsoncode = self::JSONCODE . "-$data->type";
		$json     = $jsonm->getFile($jsoncode);
		$session  = self::pw('session');

		if ($jsonm->exists($jsoncode)) {
			if (self::jsonItemidMatches($json['itemid'], $data->itemID) === false) {
				$jsonm->delete($jsoncode);
				$session->redirect(self::bomUrl($data->itemID, $data->qty, $data->type, $refresh = true), $http301 = false);
			}
			$session->setFor('ii', 'bom', 0);
			return true;
		}

		if ($session->getFor('ii', 'bom') > 3) {
			return false;
		}
		$session->setFor('ii', 'bom', ($session->getFor('ii', 'bom') + 1));
		$session->redirect(self::bomUrl($data->itemID, $data->qty, $data->type, $refresh = true), $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayBom($data) {
		$html = '';
		$html .= self::breadCrumbs();;
		$html .= self::displayData($data);
		return $html;
	}

	private static function displayData($data) {
		$jsonm    = self::getJsonModule();
		$config   = self::pw('config');
		$jsoncode = self::JSONCODE . "-$data->type";
		$json     = $jsonm->getFile($jsoncode);

		if ($jsonm->exists($jsoncode) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'BoM File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl = self::bomUrl($data->itemID, $data->qty, $data->type, $refresh = true);
		$page->lastmodified = $jsonm->lastModified($jsoncode);
		return $config->twig->render('items/ii/components/bom/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'module_json' => $jsonm->jsonm, 'type' => $data->type]);
	}

	private static function qtyForm($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$config = self::pw('config');
		$page   = self::pw('page');

		$page->headline = "II: $data->itemID BoM";
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		$html = self::breadCrumbs();
		$html .= $config->twig->render('items/ii/components/bom/qty-form.twig', ['itemID' => $data->itemID]);
		return $html;
	}
}
