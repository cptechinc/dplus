<?php namespace Controllers\Mii\Ii;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ii\PurchaseHistory as Formatter;
// Dplus Document Management
use Dplus\DocManagement\Finders\ApInvoice as Docm;

class PurchaseHistory extends Base {
	const JSONCODE          = 'ii-purchase-history';
	const PERMISSION_IIO    = 'purchasehistory';
	const DATE_FORMAT       = 'm/d/Y';
	const DATE_FORMAT_DPLUS = 'Ymd';

/* =============================================================
	Indexes
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
		return self::initScreen($data);
	}

	private static function history($data) {
		self::getData($data);
		self::pw('page')->headline = "II: $data->itemID Purchase History";
		return self::displayHistory($data);
	}

	private static function initScreen($data) {
		$iio    = self::getIio();

		$options = $iio->useriio(self::pw('user')->loginid);
		$data->startdate = date(self::DATE_FORMAT);

		if ($options->dayspurchasehistory > 0) {
			$data->startdate = date(self::DATE_FORMAT, strtotime("-$options->dayspurchasehistory days"));
		}

		if (intval($options->datepurchasehistory) > 0) {
			$data->startdate = date(self::DATE_FORMAT, strtotime($options->datepurchasehistory));
		}
		self::pw('page')->headline = "II: $data->itemID Purchase History";
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		return self::displayInitialForm($data);
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
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
	URLs
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
	Data Retrieval
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
			if (self::jsonItemidMatches($json['itemid'], $data->itemID) === false || $json['date'] != date(self::DATE_FORMAT_DPLUS, $data->timestamp)) {
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
	Displays
============================================================= */
	private static function displayHistory($data) {
		$html = '';
		$html .= self::breadCrumbs();;
		$html .= self::displayData($data);
		return $html;
	}

	protected static function displayData($data) {
		self::init();
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
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
		$docm = new Docm();
		return $config->twig->render('items/ii/purchase-history/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'module_json' => $jsonm->jsonm, 'docm' => $docm]);
	}

	private static function displayInitialForm($data) {
		$html = self::breadCrumbs();
		$html .= '<h3> Enter Starting History Date</h3>';
		$html .= self::pw('config')->twig->render('items/ii/purchase-history/date-form.twig', ['itemID' => $data->itemID, 'startdate' => $data->startdate]);
		return $html;
	}

/* =============================================================
	Hooks
============================================================= */
	private static function init() {
		$m = self::pw('modules')->get('DpagesMii');

		$m->addHook('Page(pw_template=ii-item)::documentListUrl', function($event) {
			$itemID   = $event->arguments(0);
			$ordn     = $event->arguments(1);
			$date     = $event->arguments(2);
			$event->return = Documents::documentsUrlApInvoice($itemID, $ordn, $date);
		});
	}
}
