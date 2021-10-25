<?php namespace Controllers\Mpm\Pmmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use PrWorkCenter;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
// use Dplus\Mpm\Pmmain\Dcm as DcmManager;
// Mvc Controllers
use Controllers\Mpm\Base;

class Dcm extends Base {
	const DPLUSPERMISSION = 'dcm';
	private static $dcm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['bomID|text', 'component|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	// public static function handleCRUD($data) {
	// 	$fields = ['bomID|text', 'component|text', 'action|text'];
	// 	self::sanitizeParametersShort($data, $fields);
	// 	$url  = self::bomUrl($data->bomID);
	// 	$dcm  = self::getDcm();
	//
	// 	if ($data->action) {
	// 		$dcm->processInput(self::pw('input'));
	// 	}
	//
	// 	if ($dcm->components->hasComponents($data->bomID) === false) {
	// 		$url = self::dcmUrl();
	// 	}
	// 	self::pw('session')->redirect($url, $http301 = false);
	// }

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Mpm\PrWorkCenter();

		$page->headline = "Work Center";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "DCM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, 10);
		self::initHooks();

		// $page->js .= self::pw('config')->twig->render('mpm/dcm/list/.js.twig');
		$html = self::displayList($data, $codes);
		// self::getDcm()::deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function dcmUrl($itemID = '') {
		if (empty($itemID)) {
			return Menu::dcmUrl();
		}
		return self::dcmFocusUrl($itemID);
	}

	public static function dcmFocusUrl($focus) {
		$filter = new Filters\Mpm\Bom\Header();
		if ($filter->exists($focus) === false) {
			return Menu::dcmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position);

		$url = new Purl(Menu::dcmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'dcm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::dcmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');

		$html  = '';
		// $html .= $config->twig->render('mpm/dcm/bread-crumbs.twig');
		// $html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/list.twig', ['manager' => null, 'codes' => $codes]);
		// $html .= $config->twig->render('code-tables/mpo/cnfm/edit-modal.twig', ['cnfm' => $cnfm]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		return $html;
	}

	public static function displayResponse($data) {
		$dcm = self::getDcm();
		$response = $dcm::getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('items/itm/response-alert-new.twig', ['response' => $response]);
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mpm)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getDcm() {
		return null;
		if (empty(self::$dcm)) {
			self::$dcm = new DcmManager();
		}
		return self::$dcm;
	}

	public static function lock($bomID) {
		$dcm = self::getDcm();
		return $dcm->lockrecord($bomID);
	}
}
