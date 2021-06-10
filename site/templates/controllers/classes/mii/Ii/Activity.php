<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class Activity extends IiFunction {
	const JSONCODE          = 'ii-activity';
	const PERMISSION_IIO    = 'activity';
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
			self::pw('session')->redirect(self::activityUrl($data->itemID, $data->date), $http301 = false);
		}

		if (empty($data->date) === false) {
			return self::activity($data);
		}
		return self::dateForm($data);
	}

	public static function activity($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::sanitizeParametersShort($data, ['itemID|text']);

		self::getData($data);
		$page    = self::pw('page');
		$page->headline = "II: $data->itemID Activity";
		$html = '';
		$html .= self::breadCrumbs();;
		$html .= self::display($data);
		return $html;
	}

/* =============================================================
	2. Data Requests
============================================================= */
	public static function requestJson($vars) {
		$fields = ['itemID|text', 'date|date', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['IIACTIVITY', "ITEMID=$vars->itemID"];
		if ($vars->date) {
			$dateYmd = date(self::DATE_FORMAT_DPLUS, $vars->date);
			$data[] = "DATE=$dateYmd";
		}
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	3. URLs
============================================================= */
	public static function activityUrl($itemID, $date = '', $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('activity');
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
		$session = self::pw('session');

		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['itemid'] != $data->itemID || $json['date'] != date(self::DATE_FORMAT_DPLUS, $data->timestamp)) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::activityUrl($data->itemID, $data->date, $refresh = true), $http301 = false);
			}
			$session->setFor('ii', 'activity', 0);
			return true;
		}

		if ($session->getFor('ii', 'activity') > 3) {
			return false;
		}
		$session->setFor('ii', 'activity', ($session->getFor('ii', 'activity') + 1));
		$session->redirect(self::activityUrl($data->itemID, $data->date, $refresh = true), $http301 = false);
	}

/* =============================================================
	5. Displays
============================================================= */
	protected static function display($data) {
		self::init();
		self::sanitizeParametersShort($data, ['itemID|text', 'date|text']);
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');


		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Activity File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$docm = Documents::getDocFinderIi();
		$page->refreshurl   = self::activityUrl($data->itemID, $data->date, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		return $config->twig->render('items/ii/activity/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'module_json' => $jsonm->jsonm, 'docm' => $docm, 'date' => $data->date]);
	}

	private static function dateForm($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$config = self::pw('config');
		$page   = self::pw('page');
		$iio    = self::getIio();

		$options = $iio->useriio(self::pw('user')->loginid);
		$startdate = date(self::DATE_FORMAT);

		if ($options->daysactivity > 0) {
			$startdate = date(self::DATE_FORMAT, strtotime("-$options->daysactivity days"));
		}

		if (intval($options->dateactivity) > 0) {
			$startdate = date(self::DATE_FORMAT, strtotime($options->dateactivity));
		}

		$page->headline = "II: $data->itemID Activity";
		$html = self::breadCrumbs();
		$html .= '<h3> Enter Starting Activity Date</h3>';
		$html .= $config->twig->render('items/ii/activity/date-form.twig', ['itemID' => $data->itemID, 'startdate' => $startdate]);
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		return $html;
	}

	public static function init() {
		$m = self::pw('modules')->get('DpagesMii');
		$m->addHook('Page(pw_template=ii-item)::documentListUrl', function($event) {
			$page      = $event->object;
			$itemID    = $event->arguments(0);
			$type      = $event->arguments(1);
			$reference = $event->arguments(2);

			$url = new Purl(Documents::documentsUrl($itemID, 'ACT'));
			$url->query->set('type', $type);
			$url->query->set('reference', $reference);
			$event->return = $url->getUrl();
		});
	}
}
