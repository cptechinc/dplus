<?php namespace Controllers\Mgl\Glmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use GlDistCode;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mgl\Dtm as DtmManager;
// Mvc Controllers
use Controllers\Mgl\Glmain\Base;

class Dtm extends Base {
	const DPLUSPERMISSION = 'dtm';
	const SHOWONPAGE = 10;

	private static $dtm;

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
		$url  = self::dtmUrl();
		$dtm  = self::getDtm();

		if ($data->action) {
			$dtm->processInput(self::pw('input'));
			$url  = self::dtmUrl($data->code);
		}

		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Mgl\GlDistCode();

		$page->headline = "Distribution Code";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "DTM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/mgl/dtm/.js.twig', ['dtm' => self::getDtm()]);
		$html = self::displayList($data, $codes);
		self::getDtm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function dtmUrl($code = '') {
		if (empty($code)) {
			return Menu::dtmUrl();
		}
		return self::dtmFocusUrl($code);
	}

	public static function dtmFocusUrl($focus) {
		$filter = new Filters\Mgl\GlDistCode();
		if ($filter->exists($focus) === false) {
			return Menu::dtmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr  = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::dtmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'dtm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::dtmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$dtm = self::getDtm();

		$html  = '';
		$html .= $config->twig->render('code-tables/mgl/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/mgl/dtm/display.twig', ['manager' => $dtm, 'codes' => $codes]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		$html .= $config->twig->render('code-tables/mgl/dtm/edit-modal.twig', ['manager' => $dtm]);
		return $html;
	}

	public static function displayResponse($data) {
		$dtm = self::getDtm();
		$response = $dtm->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=mgl)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=mgl)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getDtm() {
		if (empty(self::$dtm)) {
			self::$dtm = new DtmManager();
		}
		return self::$dtm;
	}
}