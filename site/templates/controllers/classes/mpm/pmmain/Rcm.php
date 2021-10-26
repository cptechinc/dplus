<?php namespace Controllers\Mpm\Pmmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use PrResource;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mpm\Rcm as RcmManager;
// Mvc Controllers
use Controllers\Mpm\Base;

class Rcm extends Base {
	const DPLUSPERMISSION = 'rcm';
	const SHOWONPAGE = 10;

	private static $rcm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::rcmUrl($data->code);
		$rcm  = self::getRcm();

		if ($data->action) {
			$rcm->processInput(self::pw('input'));
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Mpm\PrResource();

		$page->headline = "Work Center";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "DCM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/mpm/rcm/.js.twig', ['rcm' => self::getRcm()]);
		$html = self::displayList($data, $codes);
		self::getRcm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function rcmUrl($code = '') {
		if (empty($code)) {
			return Menu::rcmUrl();
		}
		return self::rcmFocusUrl($code);
	}

	public static function rcmFocusUrl($focus) {
		$filter = new Filters\Mpm\PrResource();
		if ($filter->exists($focus) === false) {
			return Menu::rcmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::rcmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'rcm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::rcmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$rcm = self::getRcm();

		$html  = '';
		$html .= $config->twig->render('code-tables/mpm/rcm/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/list.twig', ['manager' => $rcm, 'codes' => $codes]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		$html .= $config->twig->render('code-tables/mpm/rcm/edit-modal.twig', ['rcm' => $rcm]);
		return $html;
	}

	public static function displayResponse($data) {
		$rcm = self::getRcm();
		$response = $rcm->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mpm)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=mpm)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getRcm() {
		if (empty(self::$rcm)) {
			self::$rcm = new RcmManager();
		}
		return self::$rcm;
	}
}
