<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// Dplus Model
use InvKitQuery, InvKit;
use InvKitComponentQuery, InvKitComponent;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class Kit extends IiFunction {
	const JSONCODE          = 'ii-components-kit';
	const PERMISSION_IIO    = 'kit';
	const DATE_FORMAT       = 'm/d/Y';
	const DATE_FORMAT_DPLUS = 'Ymd';

	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool', 'qty|int'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}


		if ($data->refresh) {
			self::requestJson($data, session_id());
			self::pw('session')->redirect(self::kitUrl($data->itemID, $data->qty), $http301 = false);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();

		if (empty($data->qty) === false) {
			return self::kit($data);
		}
		return self::qtyForm($data);
	}

	public static function requestJson($vars) {
		$fields = ['itemID|text', 'qty|int', 'sessionID|text'];
		$vars   = self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$db     = self::pw('modules')->get('DplusOnlineDatabase')->db_name;
		$data   = ["DBNAME=$db", 'IIKIT', "ITEMID=$vars->itemID", "QTYNEEDED=$vars->qty"];
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $vars->sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['default'], $vars->sessionID);
	}

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

	public static function kit($data) {
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

		$page->headline = "II: $data->itemID Kit";
		$html .= self::breadCrumbs();;
		$html .= self::kitData($data);
		return $html;
	}

	private static function kitData($data) {
		$data    = self::sanitizeParametersShort($data, ['itemID|text', 'qty|int']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$page    = self::pw('page');
		$config  = self::pw('config');
		$session = self::pw('session');
		$html = '';

		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['itemid'] != $data->itemID) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::kitUrl($data->itemID, $data->qty, $refresh = true), $http301 = false);
			}
			$session->setFor('ii', 'kit', 0);
			$refreshurl = self::kitUrl($data->itemID, $data->qty, $refresh = true);
			$html .= self::kitDataDisplay($data, $json);
			return $html;
		}

		if ($session->getFor('ii', 'kit') > 3) {
			$page->headline = "Kit File could not be loaded";
			$html .= self::kitDataDisplay($data, $json);
			return $html;
		} else {
			$session->setFor('ii', 'kit', ($session->getFor('ii', 'kit') + 1));
			$session->redirect(self::kitUrl($data->itemID, $data->qty, $refresh = true), $http301 = false);
		}
	}

	protected static function kitDataDisplay($data, $json) {
		$jsonm  = self::getJsonModule();
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
		$html =  '';
		$components = InvKitComponentQuery::create()->filterByKitid($data->itemID)->find();
		$html .= $config->twig->render('items/ii/components/kit/display.twig', ['item' => self::getItmItem($data->itemID),  'components' => $components, 'json' => $json, 'module_json' => $jsonm->jsonm,]);
		return $html;
	}

	private static function qtyForm($data) {
		$data = self::sanitizeParametersShort($data, ['itemID|text']);
		$config = self::pw('config');
		$page = self::pw('page');

		$page->headline = "II: $data->itemID Kit";
		$html = self::breadCrumbs();
		$html .= $config->twig->render('items/ii/components/kit/qty-form.twig', ['itemID' => $data->itemID]);
		//$page->js = $config->twig->render('items/ii/pricing/customer/form.js.twig');
		$config->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		return $html;
	}

}
