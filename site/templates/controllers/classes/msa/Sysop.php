<?php namespace Controllers\Msa;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use ProspectSource;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Msa\Sysop as SysopManager;

class Sysop extends Base {
	const DPLUSPERMISSION = 'sysop';
	const SHOWONPAGE = 10;

	private static $sysop;

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
		$url  = self::sysopUrl();
		$sysop  = self::getSysop();

		if ($data->action) {
			$sysop->processInput(self::pw('input'));
			$url  = self::sysopUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Msa\MsaSysopCode();

		$page->headline = "System Optional Codes";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "SYSOP: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
		self::initHooks();

		// $page->js .= self::pw('config')->twig->render('code-tables/msa/sysop/.js.twig', ['sysop' => self::getSysop()]);
		$html = self::displayList($data, $codes);
		self::getSysop()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function sysopUrl($code = '') {
		if (empty($code)) {
			return Menu::sysopUrl();
		}
		return self::sysopFocusUrl($code);
	}

	public static function sysopFocusUrl($focus) {
		$filter = new Filters\Msa\SysLoginGroup();
		if ($filter->exists($focus) === false) {
			return Menu::sysopUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::sysopUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'sysop', $pagenbr);
		return $url->getUrl();
	}

	public static function codeEditUrl($system, $code) {
		$url = new Purl(Menu::sysopUrl());
		$url->query->set('system', $system);
		$url->query->set('code', $code);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($ystem, $code) {
		$url = new Purl(Menu::sysopUrl());
		$url->query->set('system', $system);
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$sysop = self::getSysop();

		$html  = '';
		// $html .= $config->twig->render('code-tables/msa/sysop/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/msa/sysop/list.twig', ['manager' => $sysop, 'codes' => $codes]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $sysop]);
		return $html;
	}

	public static function displayResponse($data) {
		$sysop = self::getSysop();
		$response = $sysop->getResponse();
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

		$m->addHook('Page(pw_template=msa)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=msa)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=msa)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=msa)::codeEditUrl', function($event) {
			$event->return = self::codeEditUrl($event->arguments(0), $event->arguments(1));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getSysop() {
		if (empty(self::$sysop)) {
			self::$sysop = new SysopManager();
		}
		return self::$sysop;
	}
}
