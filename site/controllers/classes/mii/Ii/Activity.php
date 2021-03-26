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

	public static function index($data) {
		$fields = ['itemID|text', 'refresh|bool', 'date|date'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data, session_id());
			if ($data->date) {
				$data->date = date(self::DATE_FORMAT, $data->date);
			}
			self::pw('session')->redirect(self::activityUrl($data->itemID, $data->date), $http301 = false);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();

		if (empty($data->date) === false) {
			return self::activity($data);
		}
		return self::dateForm($data);
	}

	public static function requestJson($vars) {
		$fields = ['itemID|text', 'date|date', 'sessionID|text'];
		$vars = self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$db = self::pw('modules')->get('DplusOnlineDatabase')->db_name;
		$data = ["DBNAME=$db", 'IIACTIVITY', "ITEMID=$vars->itemID"];
		if ($vars->date) {
			$dateYmd = date(self::DATE_FORMAT_DPLUS, $vars->date);
			$data[] = "DATE=$dateYmd";
		}
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $vars->sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['default'], $vars->sessionID);
	}

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

	public static function activity($data) {
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

		$page->headline = "$data->itemID Activity";
		$html .= self::breadCrumbs();;
		$html .= self::activityData($data);
		return $html;
	}

	private static function activityData($data) {
		$data    = self::sanitizeParametersShort($data, ['itemID|text', 'date|date']);
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
				$session->redirect(self::activityUrl($data->itemID, $data->date, $refresh = true), $http301 = false);
			}
			$session->setFor('ii', 'activity', 0);
			$refreshurl = self::activityUrl($data->itemID, $data->date, $refresh = true);
			$html .= self::activityDataDisplay($data, $json);
			return $html;
		}

		if ($session->getFor('ii', 'activity') > 3) {
			$page->headline = "Activity File could not be loaded";
			$html .= self::activityDataDisplay($data, $json);
			return $html;
		} else {
			$session->setFor('ii', 'activity', ($session->getFor('ii', 'activity') + 1));
			$session->redirect(self::activityUrl($data->itemID, $data->date, $refresh = true), $http301 = false);
		}
	}

	protected static function activityDataDisplay($data, $json) {
		$jsonm  = self::getJsonModule();
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Activity File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$docm = self::pw('modules')->get('DocumentManagementIi');
		$page->refreshurl = self::activityUrl($data->itemID, $data->date, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$html =  '';
		$html .= $config->twig->render('items/ii/activity/display.twig', ['item' => self::getItmItem($data->itemID), 'json' => $json, 'module_json' => $jsonm->$jsonm, 'docm' => $docm, 'date' => $data->date]);
		return $html;
	}

	private static function dateForm($data) {
		$data = self::sanitizeParametersShort($data, ['itemID|text']);
		$config = self::pw('config');
		$page = self::pw('page');

		$iio = self::getIio();
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
		$config->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		return $html;
	}

}
