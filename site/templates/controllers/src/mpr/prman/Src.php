<?php namespace Controllers\Mpr\Prman;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use ProspectSource;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mpr\Src as SrcManager;
// Mvc Controllers
use Controllers\Mpr\Prman\Base;

class Src extends Base {
	const DPLUSPERMISSION = 'src';
	const SHOWONPAGE = 10;

	private static $src;

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
		$url  = self::srcUrl();
		$src  = self::getSrc();

		if ($data->action) {
			$src->processInput(self::pw('input'));
			$url  = self::srcUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Mpr\ProspectSource();

		$page->headline = "Source Code";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "SRC: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/mpr/src/.js.twig', ['src' => self::getSrc()]);
		$html = self::displayList($data, $codes);
		self::getSrc()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function srcUrl($code = '') {
		if (empty($code)) {
			return Menu::srcUrl();
		}
		return self::srcFocusUrl($code);
	}

	public static function srcFocusUrl($focus) {
		$filter = new Filters\Mpr\ProspectSource();
		if ($filter->exists($focus) === false) {
			return Menu::srcUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::srcUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'src', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::srcUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$src = self::getSrc();

		$html  = '';
		$html .= $config->twig->render('code-tables/mpr/src/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/list.twig', ['manager' => $src, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $src]);
		return $html;
	}

	public static function displayResponse($data) {
		$src = self::getSrc();
		$response = $src->getResponse();
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

		$m->addHook('Page(pw_template=mpr)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=mpr)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=mpr)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getSrc() {
		if (empty(self::$src)) {
			self::$src = new SrcManager();
		}
		return self::$src;
	}
}
