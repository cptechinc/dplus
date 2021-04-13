<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ii\PurchaseHistory as Formatter;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class PurchaseHistory extends IiFunction {
	const JSONCODE          = 'ii-purchase-history';
	const PERMISSION_IIO    = 'purchasehistory';
	const DATE_FORMAT       = 'm/d/Y';
	const DATE_FORMAT_DPLUS = 'Ymd';

/* =============================================================
	1. Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool', 'date|date'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			if ($data->date) {
				self::requestJson($data);
				$data->date = date(self::DATE_FORMAT, $data->date);
			}
			self::pw('session')->redirect(self::historyUrl($data->itemID, $data->date), $http301 = false);
		}

		if (empty($data->date) === false) {
			return self::history($data);
		}
		return self::dateForm($data);
	}

	public static function history($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::sanitizeParametersShort($data, ['itemID|text', 'date|text']);
		self::getData($data);

		$page    = self::pw('page');
		$page->headline = "II: $data->itemID Purchase History";
		$html = '';
		$html .= self::breadCrumbs();;
		$html .= self::display($data);
		return $html;
	}

/* =============================================================
	2. Data Requests
============================================================= */
	public static function requestJson($vars) {
		$fields = ['itemID|text', 'sessionID|text', 'date|date'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['IIPURCHHIST', "ITEMID=$vars->itemID"];
		if ($vars->date) {
			$dateYmd = date(self::DATE_FORMAT_DPLUS, $vars->date);
			$data[] = "DATE=$dateYmd";
		}
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	3. URLs
============================================================= */
	public static function historyUrl($itemID, $date, $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('purchase-history');
		$url->query->set('itemID', $itemID);

		if ($date) {
			$url->query->set('date', $date);

			if ($refreshdata) {
				$url->query->set('refresh', 'true');
			}
		}
		return $url->getUrl();
	}

/* =============================================================
	4. Data Retrieval
============================================================= */
	private static function getData($data) {
		self::sanitizeParametersShort($data, ['itemID|text', 'date|date']);
		if ($data->date) {
			$data->timestamp = $data->date;
			$data->date = date(self::DATE_FORMAT, $data->date);
		}
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$page    = self::pw('page');
		$config  = self::pw('config');
		$session = self::pw('session');
		$html = '';

		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['itemid'] != $data->itemID || $json['date'] != date(self::DATE_FORMAT_DPLUS, $data->timestamp)) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::historyUrl($data->itemID, $data->date, $refresh = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ii', 'purchase-history') > 3) {
			return false;
		} else {
			$session->setFor('ii', 'purchase-history', ($session->getFor('ii', 'purchase-history') + 1));
			$session->redirect(self::historyUrl($data->itemID, $data->date, $refresh = true), $http301 = false);
		}
	}

/* =============================================================
	5. Displays
============================================================= */
	protected static function display($data) {
		self::init();
		$jsonm  = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Sales Orders File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl = self::historyUrl($data->itemID, $data->date, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$formatter = new Formatter();
		$formatter->init_formatter();
		$docm = self::pw('modules')->get('DocumentManagementPo');
		return $config->twig->render('items/ii/purchase-history/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'module_json' => $jsonm->jsonm, 'docm' => $docm]);
	}

	private static function dateForm($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$config = self::pw('config');
		$page   = self::pw('page');
		$iio    = self::getIio();

		$options = $iio->useriio(self::pw('user')->loginid);
		$startdate = date(self::DATE_FORMAT);

		if ($options->dayspurchasehistory > 0) {
			$startdate = date(self::DATE_FORMAT, strtotime("-$options->dayspurchasehistory days"));
		}

		if (intval($options->datepurchasehistory) > 0) {
			$startdate = date(self::DATE_FORMAT, strtotime($options->datepurchasehistory));
		}

		$page->headline = "II: $data->itemID Purchase History";
		$html = self::breadCrumbs();
		$html .= '<h3> Enter Starting History Date</h3>';
		$html .= $config->twig->render('items/ii/purchase-history/date-form.twig', ['itemID' => $data->itemID, 'startdate' => $startdate]);
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		return $html;
	}

/* =============================================================
	6. Supplements
============================================================= */
	public static function init() {
		$m = self::pw('modules')->get('DpagesMii');

		$m->addHook('Page(pw_template=ii-item)::documentListUrl', function($event) {
			$itemID   = $event->arguments(0);
			$ordn     = $event->arguments(1);
			$date     = $event->arguments(2);
			$event->return = Documents::documentsUrlApInvoice($itemID, $ordn, $date);
		});
	}
}
