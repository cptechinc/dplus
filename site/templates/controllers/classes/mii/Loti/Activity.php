<?php namespace Controllers\Mii\Loti;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use InvLotMasterQuery, InvLotMaster;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\Min\LotMaster as LotFilter;
// Mvc Controllers
use Mvc\Controllers\Controller;
use Controllers\Mii\Ii\Activity as IiActivity;
use Controllers\Mii\Ii\Documents as IiDocuments;

use Controllers\Mii\Loti\Base;

class Activity extends Base {

	public static function index($data) {
		$fields = ['lotnbr|text', 'startdate|date', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		if (empty($data->lotnbr)) {
			// TODO redirect
		}

		$filter = self::getFilter();

		if ($filter->query->filterByLotnbr($data->lotnbr)->count() === 0) {
			return self::invalidLotDisplay($data);
		}

		self::requestJson($data);
		return self::activity($data);
	}

	private static function activity($data) {
		self::getData($data);
		self::initHooks();

		$page    = self::pw('page');
		$page->headline = "LOTI: $data->lotnbr Activity";

		$html = '';
		$html .= self::display($data);
		return $html;
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getLotItemid($lotnbr) {
		$filter = self::getFilter();
		$filter->query->select(InvLotMaster::aliasproperty('itemid'));
		$filter->query->filterByLotnbr($lotnbr);
		return $filter->query->findOne();
	}

	private static function setupData($data) {
		$data->itemID    = self::getLotItemid($data->lotnbr);
		$data->lotserial = $data->lotnbr;
		$data->date      = 0;
	}

	private static function requestJson($data) {
		self::setupData($data);
		IiActivity::requestJson($data);
	}

	private static function getData($data) {
		self::sanitizeParametersShort($data, ['lotnbr|text', 'startdate|date']);
		self::setupData($data);

		$jsonm = IIActivity::getJsonModule();
		$json   = $jsonm->getFile(IIActivity::JSONCODE);
		$session = self::pw('session');

		if ($jsonm->exists(IIActivity::JSONCODE) === false) {
			$session->redirect(self::activityUrl($data->lotnbr, $refresh = true));
		}

		if ($jsonm->exists(IIActivity::JSONCODE)) {
			if (IIActivity::jsonItemidMatches($json['itemid'], $data->itemID) === false) {
				$jsonm->delete(IIActivity::JSONCODE);
				$session->redirect(self::activityUrl($data->lotnbr, $refresh = true), $http301 = false);
			}
			$session->setFor('loti', 'activity', 0);
			return true;
		}

		if ($session->getFor('loti', 'activity') > 3) {
			return false;
		}
		$session->setFor('loti', 'activity', ($session->getFor('loti', 'activity') + 1));
		$session->redirect(self::activityUrl($data->lotnbr, $refresh = true), $http301 = false);
	}

/* =============================================================
	URLs
============================================================= */
	public static function activityUrl($lotnbr, $refreshdata = false) {
		$url = new Purl(Loti::lotActivityUrl($lotnbr));
		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function display($data) {
		$jsonm = IIActivity::getJsonModule();
		$json   = $jsonm->getFile(IIActivity::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(IIActivity::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Activity File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$docm = IiDocuments::getDocFinderIi();
		$page->refreshurl   = self::activityUrl($data->lotnbr, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(IIActivity::JSONCODE);

		$lot = self::getLot($data->lotnbr);

		$html  = self::breadcrumbs($data);
		$html .= $config->twig->render('mii/loti/activity/display.twig', ['json' => $json, 'module_json' => $jsonm->jsonm, 'docm' => $docm, 'lot' => $lot]);
		return $html;
	}

	private static function invalidLotDisplay($data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "$data->lotnbr not found in Lot Master"]);
	}

	private static function dateForm($data) {
		self::sanitizeParametersShort($data, ['lotnbr|text']);
		$config = self::pw('config');
		$page   = self::pw('page');
		$iio    = self::pw('modules')->get('Iio');

		$options = $iio->useriio(self::pw('user')->loginid);
		$startdate = date(IiActivity::DATE_FORMAT);

		if ($options->daysactivity > 0) {
			$startdate = date(IiActivity::DATE_FORMAT, strtotime("-$options->daysactivity days"));
		}

		if (intval($options->dateactivity) > 0) {
			$startdate = date(IiActivity::DATE_FORMAT, strtotime($options->dateactivity));
		}

		$page->headline = "Lot $data->lotnbr Activity";
		$html  = self::breadcrumbs($data);
		$html .= '<h3> Enter Starting Activity Date</h3>';
		$html .= self::pw('config')->twig->render('mii/loti/activity/date-form.twig', ['lotnbr' => $data->lotnbr, 'startdate' => $startdate]);
		return $html;
	}

/* =============================================================
	URL Functions
============================================================= */

	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMii');

		$m->addHook('Page(pw_template=loti)::documentListUrl', function($event) {
			$page      = $event->object;
			// $itemID    = $event->arguments(0);
			// $type      = $event->arguments(1);
			// $reference = $event->arguments(2);
			//
			// $url = new Purl(Documents::documentsUrl($itemID, 'ACT'));
			// $url->query->set('type', $type);
			// $url->query->set('reference', $reference);
			// $event->return = $url->getUrl();
			$event->return = $page->url;
		});
	}
}
