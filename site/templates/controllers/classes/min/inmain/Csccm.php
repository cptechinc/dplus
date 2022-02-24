<?php namespace Controllers\Min\Inmain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use ProspectSource;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Min\Csccm as CsccmManager;
// Mvc Controllers
use Controllers\Min\Base;

class Csccm extends Base {
	const DPLUSPERMISSION = 'csccm';
	const SHOWONPAGE = 10;

	private static $csccm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = 'Customer Stocking Cell Code';

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::csccmUrl();
		$csccm = self::getCsccm();

		if ($data->action) {
			$csccm->processInput(self::pw('input'));
			$url = self::csccmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$page->headline = "Customer Stocking Cell Code";

		$filter = new Filters\Min\CustomerStockingCell();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "CSCCM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/min/csccm/.js.twig', ['csccm' => self::getCsccm()]);
		$html = self::displayList($data, $codes);
		self::getCsccm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function csccmUrl($code = '') {
		if (empty($code)) {
			return Menu::csccmUrl();
		}
		return self::csccmFocusUrl($code);
	}

	public static function csccmFocusUrl($focus) {
		$filter = new Filters\Min\CustomerStockingCell();
		if ($filter->exists($focus) === false) {
			return Menu::csccmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::csccmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'csccm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::csccmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$csccm = self::getCsccm();

		$html  = '';
		$html .= $config->twig->render('code-tables/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/list.twig', ['manager' => $csccm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $csccm]);
		return $html;
	}

	public static function displayResponse($data) {
		$csccm = self::getCsccm();
		$response = $csccm->getResponse();
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
	public static function getCsccm() {
		if (empty(self::$csccm)) {
			self::$csccm = new CsccmManager();
		}
		return self::$csccm;
	}
}
