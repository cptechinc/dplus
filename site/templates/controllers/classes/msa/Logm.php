<?php namespace Controllers\Msa;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use DplusUser;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Msa\Logm as LogmManager;

class Logm extends Base {
	const DPLUSPERMISSION = 'logm';
	const SHOWONPAGE = 10;

	private static $logm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['id|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		if (empty($data->id) === false) {
			return self::user($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['id|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::logmUrl();
		$logm  = self::getLogm();

		if ($data->action) {
			$logm->processInput(self::pw('input'));
			$url  = self::logmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Msa\DplusUser();

		$page->headline = "Login ID Entry";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "LOGM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
		self::initHooks();

		//$page->js .= self::pw('config')->twig->render('code-tables/msa/logm/.js.twig', ['logm' => self::getLogm()]);
		$html = self::displayList($data, $codes);
		// self::getLogm()->deleteResponse();
		return $html;
	}

	private static function user($data) {
		$logm = self::getLogm();
		$page = self::pw('page');
		$page->headline = "LOGM: $data->id";

		if ($logm->exists($data->id) === false) {
			$page->headline = "LOGM: Creating New User";
		}
		$user = $logm->getOrCreate($data->id);
		$html = self::displayUser($data, $user);
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function logmUrl($code = '') {
		if (empty($code)) {
			return Menu::logmUrl();
		}
		return self::logmFocusUrl($code);
	}

	public static function logmFocusUrl($focus) {
		$filter = new Filters\Msa\DplusUser();
		if ($filter->exists($focus) === false) {
			return Menu::logmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::logmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'logm', $pagenbr);
		return $url->getUrl();
	}

	public static function userDeleteUrl($id) {
		$url = new Purl(Menu::logmUrl());
		$url->query->set('id', $id);
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

	public static function userEditUrl($id) {
		$url = new Purl(Menu::logmUrl());
		$url->query->set('id', $id);
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $users) {
		$config = self::pw('config');
		$logm   = self::getLogm();

		$html  = '';
		// $html .= $config->twig->render('code-tables/msa/logm/bread-crumbs.twig');
		// $html .= self::displayResponse($data);
		$html .= $config->twig->render('msa/logm/list.twig', ['logm' => $logm, 'users' => $users]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $users]);
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $logm]);
		return $html;
	}

	public static function displayResponse($data) {
		$logm = self::getLogm();
		$response = $logm->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

	private static function displayUser($data, DplusUser $user) {
		$config = self::pw('config');
		$logm   = self::getLogm();

		$html  = '';
		$html .= $config->twig->render('msa/logm/user.twig', ['logm' => $logm, 'duser' => $user]);
		return $html;
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(template=test)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(template=test)::userEditUrl', function($event) {
			$event->return = self::userEditUrl($event->arguments(0));
		});

		$m->addHook('Page(template=test)::userDeleteUrl', function($event) {
			$event->return = self::userDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getLogm() {
		if (empty(self::$logm)) {
			self::$logm = new LogmManager();
		}
		return self::$logm;
	}
}
