<?php namespace Controllers\Min\Inmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use ProspectSource;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Min\Msdsm as MsdsmManager;
// Mvc Controllers
use Controllers\Min\Base;

class Msdsm extends Base {
	const DPLUSPERMISSION = 'msdsm';
	const SHOWONPAGE = 10;

	private static $msdsm;

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
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::msdsmUrl();
		$msdsm  = self::getMsdsm();

		if ($data->action) {
			$msdsm->processInput(self::pw('input'));
			$url = self::msdsmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$page->headline = "Material Safety Data Sheet Code";

		$filter = new Filters\Min\MsdsCode();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "MSDSM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/min/msdsm/.js.twig', ['msdsm' => self::getMsdsm()]);
		$html = self::displayList($data, $codes);
		self::getMsdsm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function msdsmUrl($code = '') {
		if (empty($code)) {
			return Menu::msdsmUrl();
		}
		return self::msdsmFocusUrl($code);
	}

	public static function msdsmFocusUrl($focus) {
		$filter = new Filters\Min\MsdsCode();
		if ($filter->exists($focus) === false) {
			return Menu::msdsmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::msdsmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'msdsm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::msdsmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$msdsm = self::getMsdsm();

		$html  = '';
		$html .= $config->twig->render('code-tables/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/min/msdsm/list.twig', ['manager' => $msdsm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/min/msdsm/edit-modal.twig', ['manager' => $msdsm]);
		return $html;
	}

	public static function displayResponse($data) {
		$msdsm = self::getMsdsm();
		$response = $msdsm->getResponse();
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
	public static function getMsdsm() {
		if (empty(self::$msdsm)) {
			self::$msdsm = new MsdsmManager();
		}
		return self::$msdsm;
	}
}
