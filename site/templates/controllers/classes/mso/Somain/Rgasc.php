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
use Dplus\Codes\Mso\Rgasc as RgascManager;
// Mvc Controllers
use Controllers\Mso\Somain\Base;

class Rgasc extends Base {
	const DPLUSPERMISSION = 'rgasc';
	const SHOWONPAGE = 10;

	private static $rgasc;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = 'RGA/Return Ship Via Code';

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::rgascUrl();
		$rgasc  = self::getRgasc();

		if ($data->action) {
			$rgasc->processInput(self::pw('input'));
			$url = self::rgascUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$page->headline = "RGA/Return Ship Via Code";

		$filter = new Filters\Mso\SoRgaCode();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "RGASC: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/mso/rgasc/.js.twig', ['rgasc' => self::getRgasc()]);
		$html = self::displayList($data, $codes);
		self::getRgasc()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function rgascUrl($code = '') {
		if (empty($code)) {
			return Menu::rgascUrl();
		}
		return self::rgascFocusUrl($code);
	}

	public static function rgascFocusUrl($focus) {
		$filter = new Filters\Mso\SoRgaCode();
		if ($filter->exists($focus) === false) {
			return Menu::rgascUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::rgascUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'rgasc', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::rgascUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$rgasc = self::getRgasc();

		$html  = '';
		$html .= $config->twig->render('code-tables/mso/rgasc/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/mso/rgasc/list.twig', ['manager' => $rgasc, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/mso/rgasc/edit-modal.twig', ['manager' => $rgasc]);
		return $html;
	}

	public static function displayResponse($data) {
		$rgasc = self::getRgasc();
		$response = $rgasc->getResponse();
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
	public static function getRgasc() {
		if (empty(self::$rgasc)) {
			self::$rgasc = new RgascManager();
		}
		return self::$rgasc;
	}
}
