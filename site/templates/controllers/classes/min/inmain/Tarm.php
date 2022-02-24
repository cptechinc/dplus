<?php namespace Controllers\Min\Inmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Min\Tarm as TarmManager;
// Mvc Controllers
use Controllers\Min\Base;

class Tarm extends Base {
	const DPLUSPERMISSION = 'tarm';
	const SHOWONPAGE = 10;

	private static $tarm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = 'Tariff Code';

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::tarmUrl();
		$tarm  = self::getTarm();

		if ($data->action) {
			$tarm->processInput(self::pw('input'));
			$url  = self::tarmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Min\TariffCode();

		$page->headline = "Tariff Code";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "TARM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/min/tarm/.js.twig', ['tarm' => self::getTarm()]);
		$html = self::displayList($data, $codes);
		self::getTarm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function tarmUrl($code = '') {
		if (empty($code)) {
			return Menu::tarmUrl();
		}
		return self::tarmFocusUrl($code);
	}

	public static function tarmFocusUrl($focus) {
		$filter = new Filters\Min\TariffCode();
		if ($filter->exists($focus) === false) {
			return Menu::tarmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::tarmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'tarm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::tarmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$tarm = self::getTarm();

		$html  = '';
		$html .= $config->twig->render('code-tables/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/min/tarm/list.twig', ['manager' => $tarm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/min/tarm/edit-modal.twig', ['manager' => $tarm]);
		return $html;
	}

	public static function displayResponse($data) {
		$tarm = self::getTarm();
		$response = $tarm->getResponse();
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

		$m->addHook('Page(pw_template=inmain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=inmain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=inmain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getTarm() {
		if (empty(self::$tarm)) {
			self::$tarm = new TarmManager();
		}
		return self::$tarm;
	}
}
