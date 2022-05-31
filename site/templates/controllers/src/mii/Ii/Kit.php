<?php namespace Controllers\Mii\Ii;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use InvKitQuery, InvKit;
use InvKitComponentQuery, InvKitComponent;

class Kit extends Base {
	const JSONCODE          = 'ii-components-kit';
	const PERMISSION_IIO    = 'kit';
	const DATE_FORMAT       = 'm/d/Y';
	const DATE_FORMAT_DPLUS = 'Ymd';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool', 'qty|int'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data, session_id());
			self::pw('session')->redirect(self::kitUrl($data->itemID, $data->qty), $http301 = false);
		}

		if (empty($data->qty) === false) {
			return self::kit($data);
		}
		return self::initScreen($data);
	}

	private static function kit($data) {
		self::getData($data);
		self::pw('page')->headline = "II: $data->itemID Kit";
		return self::displayKit($data);
	}

	private static function initScreen($data) {
		self::pw('page')->headline = "II: $data->itemID Kit";
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		return self::displayQtyform($data);
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['itemID|text', 'qty|int', 'sessionID|text'];
		$vars   = self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data   = ['IIKIT', "ITEMID=$vars->itemID", "QTYNEEDED=$vars->qty"];
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	URLs
============================================================= */
	public static function kitUrl($itemID, $qty = 0, $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('kit');
		$url->query->set('itemID', $itemID);

		if ($qty) {
			$url->query->set('qty', $qty);

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
		$data    = self::sanitizeParametersShort($data, ['itemID|text', 'qty|int']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');

		if ($jsonm->exists(self::JSONCODE)) {
			if (self::jsonItemidMatches($json['itemid'], $data->itemID) === false) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::kitUrl($data->itemID, $data->qty, $refresh = true), $http301 = false);
			}
			$session->setFor('ii', 'kit', 0);
			return true;
		}

		if ($session->getFor('ii', 'kit') > 3) {
			return false;
		}
		$session->setFor('ii', 'kit', ($session->getFor('ii', 'kit') + 1));
		$session->redirect(self::kitUrl($data->itemID, $data->qty, $refresh = true), $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayKit($data) {
		$html = '';
		$html .= self::breadCrumbs();;
		$html .= self::displayData($data);
		return $html;
	}

	protected static function displayData($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Kit File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl = self::kitUrl($data->itemID, $data->qty, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$components = InvKitComponentQuery::create()->filterByKitid($data->itemID)->find();
		$html =  '';
		$html .= $config->twig->render('items/ii/components/kit/display.twig', ['item' => self::getItmItem($data->itemID),  'components' => $components, 'json' => $json, 'module_json' => $jsonm->jsonm,]);
		return $html;
	}

	private static function displayQtyform($data) {
		$html = self::breadCrumbs();
		$html .= self::pw('config')->twig->render('items/ii/components/kit/qty-form.twig', ['itemID' => $data->itemID]);
		return $html;
	}
}
