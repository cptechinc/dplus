<?php namespace Controllers\Mgl\Glmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use GlTextCode;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mgl\Ttm as TtmManager;
// Mvc Controllers
use Controllers\Mgl\Glmain\Base;

class Ttm extends Base {
	const DPLUSPERMISSION = 'ttm';
	const SHOWONPAGE = 10;

	private static $ttm;

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
		$url  = self::ttmUrl($data->code);
		$ttm  = self::getTtm();

		if ($data->action) {
			$ttm->processInput(self::pw('input'));
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Mgl\GlTextCode();

		$page->headline = "Source Code";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "SRC: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/mgl/ttm/.js.twig', ['ttm' => self::getTtm()]);
		$html = self::displayList($data, $codes);
		self::getTtm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function ttmUrl($code = '') {
		if (empty($code)) {
			return Menu::ttmUrl();
		}
		return self::ttmFocusUrl($code);
	}

	public static function ttmFocusUrl($focus) {
		$filter = new Filters\Mgl\GlTextCode();
		if ($filter->exists($focus) === false) {
			return Menu::ttmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::ttmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'ttm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::ttmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$ttm = self::getTtm();

		$html  = '';
		$html .= $config->twig->render('code-tables/mgl/ttm/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/list.twig', ['manager' => $ttm, 'codes' => $codes]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $ttm]);
		return $html;
	}

	public static function displayResponse($data) {
		$ttm = self::getTtm();
		$response = $ttm->getResponse();
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
	public static function getTtm() {
		if (empty(self::$ttm)) {
			self::$ttm = new TtmManager();
		}
		return self::$ttm;
	}
}
