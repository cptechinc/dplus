<?php namespace Controllers\Mar\Armain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mar\Crtm as CrtmManager;
// Mvc Controllers
use Controllers\Mar\AbstractController as Base;

class Crtm extends Base {
	const DPLUSPERMISSION = 'crtm';
	const SHOWONPAGE = 10;

	private static $crtm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = 'Customer Route Code';

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::crtmUrl();
		$crtm = self::getCrtm();

		if ($data->action) {
			$crtm->processInput(self::pw('input'));
			$url = self::crtmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text', 'col|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$page->headline = "Customer Route Code";

		$filter = new Filters\Mar\ArRouteCode();

		if (empty($data->q) === false) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();
		
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/modal-events.js'));
		$page->js .= self::pw('config')->twig->render('code-tables/mar/crtm/.js.twig', ['crtm' => self::getCrtm()]);
		$html = self::displayList($data, $codes);
		self::getCrtm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function crtmUrl($code = '') {
		if (empty($code)) {
			return Menu::crtmUrl();
		}
		return self::crtmFocusUrl($code);
	}

	public static function crtmFocusUrl($focus) {
		$filter = new Filters\Mar\ArRouteCode();
		if ($filter->exists($focus) === false) {
			return Menu::crtmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::crtmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'crtm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::crtmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$crtm = self::getCrtm();

		$html  = '';
		$html .= $config->twig->render('code-tables/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/list.twig', ['manager' => $crtm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $crtm]);
		return $html;
	}

	public static function displayResponse($data) {
		$crtm = self::getCrtm();
		$response = $crtm->getResponse();
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

		$m->addHook('Page(pw_template=armain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=armain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=armain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getCrtm() {
		if (empty(self::$crtm)) {
			self::$crtm = new CrtmManager();
		}
		return self::$crtm;
	}
}
