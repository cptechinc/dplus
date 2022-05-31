<?php namespace Controllers\Mar\Armain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use ProspectSource;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mar\Cpm as CpmManager;
// Mvc Controllers
use Controllers\Mar\Armain\Base;

class Cpm extends Base {
	const DPLUSPERMISSION = 'cpm';
	const SHOWONPAGE = 20;

	private static $cpm;

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
		$url  = self::cpmUrl();
		$cpm = self::getcpm();

		if ($data->action) {
			$cpm->processInput(self::pw('input'));
			$url = self::cpmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$page->headline = "Customer Price Code";

		$filter = new Filters\Mar\ArPriceCode();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "WORM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/mar/cpm/.js.twig', ['cpm' => self::getcpm()]);
		$html = self::displayList($data, $codes);
		self::getcpm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		return Menu::cpmUrl();
	}

	public static function cpmUrl($code = '') {
		if (empty($code)) {
			return self::url();
		}
		return self::cpmFocusUrl($code);
	}

	public static function cpmFocusUrl($focus) {
		$filter = new Filters\Mar\ArPriceCode();
		if ($filter->exists($focus) === false) {
			return self::url();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(self::url());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'cpm', $pagenbr);
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
		$cpm = self::getcpm();

		$html  = '';
		$html .= $config->twig->render('code-tables/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/mar/cpm/list.twig', ['manager' => $cpm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/mar/cpm/edit-modal.twig', ['manager' => $cpm]);
		return $html;
	}

	public static function displayResponse($data) {
		$cpm = self::getcpm();
		$response = $cpm->getResponse();
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
	public static function getcpm() {
		if (empty(self::$cpm)) {
			self::$cpm = new CpmManager();
		}
		return self::$cpm;
	}
}
