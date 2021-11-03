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
use Dplus\Qnotes\Noce as NoceManager;

class Noce extends Base {
	const DPLUSPERMISSION = 'noce';
	const SHOWONPAGE = 10;

	private static $noce;

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
		$url  = self::noceUrl();
		$noce  = self::getNoce();

		if ($data->action) {
			$noce->processInput(self::pw('input'));
			$url  = self::noceUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Msa\SysLoginGroup();

		$page->headline = "Pre-defined Notes";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "NOCE: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('msa/noce/js.twig', ['noce' => self::getNoce()]);
		$html = self::displayList($data, $codes);
		self::getNoce()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function noceUrl($code = '') {
		if (empty($code)) {
			return Menu::noceUrl();
		}
		return self::noceFocusUrl($code);
	}

	public static function noceFocusUrl($focus) {
		$filter = new Filters\Msa\SysLoginGroup();
		if ($filter->exists($focus) === false) {
			return Menu::noceUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::noceUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'noce', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::noceUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$noce = self::getNoce();

		$html  = '';
		// $html .= $config->twig->render('code-tables/msa/noce/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('msa/noce/list.twig', ['qnotes' => $qnotes]);
		$html .= $config->twig->render('msa/noce/notes-modal.twig', ['qnotes' => $qnotes]);
		// $html .= $config->twig->render('code-tables/list.twig', ['manager' => $noce, 'codes' => $codes]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		// $html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $noce]);
		return $html;
	}

	public static function displayResponse($data) {
		$noce = self::getNoce();
		$response = $noce->getResponse();
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

		$m->addHook('Page(template=test)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(template=test)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getNoce() {
		if (empty(self::$noce)) {
			self::$noce = new NoceManager();
		}
		return self::$noce;
	}
}
