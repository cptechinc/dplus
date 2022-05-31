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
use Dplus\Codes\Min\Stcm as StcmManager;
// Mvc Controllers
use Controllers\Min\Base;

class Stcm extends Base {
	const DPLUSPERMISSION = 'stcm';
	const SHOWONPAGE = 10;

	private static $stcm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		if (self::validateUserPermission() === false) {
			return self::displayAlertUserPermission($data);
		}
		// Sanitize Params, parse route from params
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		self::pw('page')->show_breadcrumbs = false;
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateUserPermission() === false) {
			return self::pw('session')->redirect(self::url(), $http301 = false);
		}
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::stcmUrl();
		$stcm = self::getStcm();

		if ($data->action) {
			$stcm->processInput(self::pw('input'));
			$url = self::stcmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$page->headline = "Stock Code";

		$filter = new Filters\Min\InvStockCode();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "STCM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/min/stcm/.js.twig', ['stcm' => self::getStcm()]);
		$html = self::displayList($data, $codes);
		self::getStcm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		return Menu::stcmUrl();
	}

	public static function stcmUrl($code = '') {
		if (empty($code)) {
			return self::url();
		}
		return self::stcmFocusUrl($code);
	}

	public static function stcmFocusUrl($focus) {
		$filter = new Filters\Min\InvStockCode();
		if ($filter->exists($focus) === false) {
			return self::url();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(self::url());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'stcm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(self::url());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$stcm = self::getStcm();

		$html  = '';
		$html .= $config->twig->render('code-tables/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/list.twig', ['manager' => $stcm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $stcm]);
		return $html;
	}

	public static function displayResponse($data) {
		$stcm = self::getStcm();
		$response = $stcm->getResponse();
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
	public static function getStcm() {
		if (empty(self::$stcm)) {
			self::$stcm = new StcmManager();
		}
		return self::$stcm;
	}
}
