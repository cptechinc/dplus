<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// Dplus Model
use CustomerQuery, Customer;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class Pricing extends IiFunction {
	const JSONCODE       = 'ii-pricing';
	const PERMISSION_IIO = 'pricing';

	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool', 'custID|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data, session_id());
			self::pw('session')->redirect(self::pricingUrl($data->itemID, $data->custID), $http301 = false);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();

		if (empty($data->custID) === false) {
			return self::pricing($data);
		}
		return self::customerForm($data);
	}

	public static function requestJson($vars) {
		$fields = ['itemID|text', 'custID|text', 'sessionID|text'];
		$vars = self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$db = self::pw('modules')->get('DplusOnlineDatabase')->db_name;
		$data = ["DBNAME=$db", 'IIPRICE', "ITEMID=$vars->itemID"];
		if ($vars->custID) {
			$data[] = "CUSTID=$vars->custID";
		}
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $vars->sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['default'], $vars->sessionID);
	}

	public static function pricingUrl($itemID, $custID = '', $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('pricing');
		$url->query->set('itemID', $itemID);

		if ($custID) {
			$url->query->set('custID', $custID);

			if ($refreshdata) {
				$url->query->set('refresh', 'true');
			}
		}
		return $url->getUrl();
	}

	public static function pricing($data) {
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

		$page->headline = "$data->itemID Pricing";
		$html .= self::breadCrumbs();;
		$html .= self::pricingData($data);
		return $html;
	}

	private static function pricingData($data) {
		$data    = self::sanitizeParametersShort($data, ['itemID|text', 'custID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$page    = self::pw('page');
		$config  = self::pw('config');
		$session = self::pw('session');
		$html = '';

		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['itemid'] != $data->itemID || $json['custid'] != $data->custID) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::pricingUrl($data->itemID, $data->custID, $refresh = true), $http301 = false);
			}
			$session->setFor('ii', 'pricing', 0);
			$refreshurl = self::pricingUrl($data->itemID, $data->custID, $refresh = true);
			$html .= self::pricingDataDisplay($data, $json);
			return $html;
		}

		if ($session->getFor('ii', 'pricing') > 3) {
			$page->headline = "Pricing File could not be loaded";
			$html .= self::pricingDataDisplay($data, $json);
			return $html;
		} else {
			$session->setFor('ii', 'pricing', ($session->getFor('ii', 'pricing') + 1));
			$session->redirect(self::pricingUrl($data->itemID, $data->custID, $refresh = true), $http301 = false);
		}
	}

	protected static function pricingDataDisplay($data, $json) {
		$jsonm  = self::getJsonModule();
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Pricing File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$iim  = self::pw('modules')->get('DpagesMii');
		$page = self::pw('page');
		$page->refreshurl = self::pricingUrl($data->itemID, $data->custID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$customer = CustomerQuery::create()->findOneByCustid($data->custID);
		$html =  '';
		$html .= $config->twig->render('items/ii/pricing/display.twig', ['item' => self::getItmItem($data->itemID), 'customer' => $customer, 'json' => $json]);
		return $html;
	}

	private static function customerForm($data) {
		$data = self::sanitizeParametersShort($data, ['itemID|text', 'q|text']);
		$config = self::pw('config');
		$page = self::pw('page');

		$page->headline = "II: $data->itemID Pricing";
		$html = self::breadCrumbs();
		$html .= $config->twig->render('items/ii/pricing/customer/form.twig', ['itemID' => $data->itemID]);
		$page->js = $config->twig->render('items/ii/pricing/customer/form.js.twig');
		$config->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		return $html;
	}

}
