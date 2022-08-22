<?php namespace Controllers\Map\Apmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Map\Vtm as VtmManager;
// Mvc Controllers
use Controllers\Map\Apmain\Base;

class Vtm extends Base {
	const DPLUSPERMISSION = 'vtm';
	const SHOWONPAGE = 10;

	private static $vtm;

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
		$url  = self::vtmUrl();
		$vtm  = self::getVtm();

		if ($data->action) {
			$vtm->processInput(self::pw('input'));
			$url  = self::vtmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text', 'col|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Map\ApTypeCode();

		$page->headline = "Vendor Type Code";

		if (empty($data->q) === false) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/map/vtm/.js.twig', ['vtm' => self::getVtm()]);
		$html = self::displayList($data, $codes);
		self::getVtm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function vtmUrl($code = '') {
		if (empty($code)) {
			return Menu::vtmUrl();
		}
		return self::vtmFocusUrl($code);
	}

	public static function vtmFocusUrl($focus) {
		$filter = new Filters\Map\ApTypeCode();
		if ($filter->exists($focus) === false) {
			return Menu::vtmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::vtmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'vtm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::vtmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$vtm = self::getVtm();
		$vtm->initFieldAttributes();

		$html  = '';
		$html .= $config->twig->render('code-tables/map/vtm/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/map/vtm/list.twig', ['manager' => $vtm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/map/vtm/edit-modal.twig', ['manager' => $vtm]);
		return $html;
	}

	public static function displayResponse($data) {
		$vtm = self::getVtm();
		$response = $vtm->getResponse();
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

		$m->addHook('Page(pw_template=apmain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=apmain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=apmain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getVtm() {
		if (empty(self::$vtm)) {
			self::$vtm = new VtmManager();
		}
		return self::$vtm;
	}
}
