<?php namespace Controllers\Mso\Somain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use ProspectSource;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mso\Lsm as LsmManager;
// Mvc Controllers
use Controllers\Mso\Somain\Base;

class Lsm extends Base {
	const DPLUSPERMISSION = 'lsm';
	const SHOWONPAGE = 10;

	private static $lsm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = 'Lost Sales Reason';

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::lsmUrl();
		$lsm  = self::getLsm();

		if ($data->action) {
			$lsm->processInput(self::pw('input'));
			$url  = self::lsmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Mso\LostSalesCode();

		$page->headline = "Lost Sales Reason";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "LSM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/mso/lsm/.js.twig', ['lsm' => self::getLsm()]);
		$html = self::displayList($data, $codes);
		self::getLsm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function lsmUrl($code = '') {
		if (empty($code)) {
			return Menu::lsmUrl();
		}
		return self::lsmFocusUrl($code);
	}

	public static function lsmFocusUrl($focus) {
		$filter = new Filters\Mso\LostSalesCode();
		if ($filter->exists($focus) === false) {
			return Menu::lsmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::lsmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'lsm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::lsmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$lsm = self::getLsm();

		$html  = '';
		$html .= $config->twig->render('code-tables/mso/lsm/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/mso/lsm/list.twig', ['manager' => $lsm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/mso/lsm/edit-modal.twig', ['manager' => $lsm]);
		return $html;
	}

	public static function displayResponse($data) {
		$lsm = self::getLsm();
		$response = $lsm->getResponse();
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

		$m->addHook('Page(pw_template=somain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=somain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=somain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getLsm() {
		if (empty(self::$lsm)) {
			self::$lsm = new LsmManager();
		}
		return self::$lsm;
	}
}
