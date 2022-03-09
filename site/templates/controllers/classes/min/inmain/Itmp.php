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
use Dplus\Min\Itmp as Manager;
// Mvc Controllers
use Controllers\Min\Base;

class Itmp extends Base {
	const TITLE = 'Item Maintenance Permissions';
	const DPLUSPERMISSION = 'itmp';
	const SHOWONPAGE = 10;

	private static $itmp;

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
		$url  = self::itmpUrl();
		$itmp = self::getItmp();

		if ($data->action) {
			$itmp->processInput(self::pw('input'));
			$url = self::itmpUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->headline = self::TITLE;
		$itmp = self::getItmp();

		$filter = new Filters\Min\UserPermissionsItm();


		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "ITMP: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$users = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('min/inmain/itmp/js.twig', ['itmp' => self::getItmp()]);
		$html = self::displayList($data, $users);
		self::getItmp()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		return Menu::itmpUrl();
	}

	public static function itmpUrl($code = '') {
		if (empty($code)) {
			return self::url();
		}
		return self::itmpFocusUrl($code);
	}

	public static function itmpFocusUrl($focus) {
		$filter = new Filters\Min\UserPermissionsItm();
		if ($filter->exists($focus) === false) {
			return self::url();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(self::url());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'itmp', $pagenbr);
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
	private static function displayList($data, PropelModelPager $users) {
		$config = self::pw('config');
		$itmp = self::getItmp();

		$html  = '';
		$html .= $config->twig->render('code-tables/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('min/inmain/itmp/display.twig', ['itmp' => $itmp, 'users' => $users]);
		// $html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $itmp]);
		return $html;
	}

	public static function displayResponse($data) {
		$itmp = self::getItmp();
		$response = $itmp->getResponse();
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
	public static function getItmp() {
		if (empty(self::$itmp)) {
			self::$itmp = Manager::instance();
		}
		return self::$itmp;
	}
}
