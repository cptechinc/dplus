<?php namespace Controllers\Msa;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use MsaSysopCode;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Msa\Sysop as SysopManager;

class Sysop extends Base {
	const DPLUSPERMISSION = 'sysop';
	const SHOWONPAGE = 10;

	private static $sysop;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['system|text', 'code|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->code) === false) {
			return self::code($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['system|text', 'code|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::sysopUrl();
		$sysop  = self::getSysop();

		if ($data->action) {
			$sysop->processInput(self::pw('input'));
			$url  = self::sysopUrl(implode($sysop->recordlocker->glue(), [$data->system, $data->code]));
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text', 'system|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Msa\MsaSysopCode();

		$page->headline = "System Optional Codes";

		if (strlen($data->q) > 0) {
			$filter->search($data->q);
			$page->headline = "SYSOP: Searching for '$data->q'";
		}

		if (empty($data->system) === false) {
			$filter->system($data->system);
		}

		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);

		self::initHooks();
		$page->js .= self::pw('config')->twig->render('code-tables/msa/sysop/list/.js.twig');
		$html = self::displayList($data, $codes);
		self::getSysop()->deleteResponse();
		return $html;
	}

	private static function code($data) {
		$sysop = self::getSysop();
		$page  = self::pw('page');
		$page->headline = "SYSOP: Editing $data->system $data->code";
		$code = $sysop->getOrCreate($data->system, $data->code);

		if ($code->isNew()) {
			$page->headline = "SYSOP: Creating New Code";
		}
		$sysop->lockrecord($code);
		self::initHooks();
		$page->js .= self::pw('config')->twig->render('code-tables/msa/sysop/form/.js.twig', ['sysop' => $sysop]);
		$html = self::displayCode($data, $code);
		$sysop->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$sysop  = self::getSysop();

		$html  = '';
		$html .= $config->twig->render('code-tables/msa/sysop/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/msa/sysop/list/filter.twig', ['sysop' => $sysop]);
		$html .= $config->twig->render('code-tables/msa/sysop/list.twig', ['sysop' => $sysop, 'codes' => $codes]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		return $html;
	}

	private static function displayCode($data, MsaSysopCode $code) {
		$config = self::pw('config');
		$sysop  = self::getSysop();

		$html  = '';
		$html .= $config->twig->render('code-tables/msa/sysop/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= self::displayLock($data, $code);
		$html .= $config->twig->render('code-tables/msa/sysop/form.twig', ['sysop' => $sysop, 'code' => $code]);
		return $html;
	}

	private static function displayLock($data, MsaSysopCode $code) {
		$sysop = self::getSysop();
		$key   = $sysop->getRecordlockerKey($code);

		if ($sysop->recordlocker->isLocked($key) === false || $sysop->recordlocker->userHasLocked($key)) {
			return false;
		}

		$msg = "$data->system $data->code is being locked by " . $sysop->recordlocker->getLockingUser($key);
		$html = '';
		$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		$html .= '<div class="mb-3"></div>';
		return $html;
	}

	public static function displayResponse($data) {
		$sysop = self::getSysop();
		$response = $sysop->getResponse();
		if (empty($response) || $response->hasSuccess()) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function sysopUrl($key = '') {
		if (empty($key)) {
			return Menu::sysopUrl();
		}
		return self::sysopFocusUrl($key);
	}

	public static function sysopFocusUrl($focus) {
		$sysop  = self::getSysop();
		$filter = new Filters\Msa\MsaSysopCode();
		$keys = explode($sysop->recordlocker->glue(), $focus);

		if ($sysop->exists($keys[0], $keys[1]) === false) {
			return Menu::sysopUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::sysopUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'sysop', $pagenbr);
		return $url->getUrl();
	}

	public static function codeEditUrl($system, $code) {
		$url = new Purl(Menu::sysopUrl());
		$url->query->set('system', $system);
		$url->query->set('code', $code);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($system, $code) {
		$url = new Purl(Menu::sysopUrl());
		$url->query->set('system', $system);
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=msa)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=msa)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=msa)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=msa)::codeEditUrl', function($event) {
			$event->return = self::codeEditUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=msa)::sysopUrl', function($event) {
			$event->return = self::sysopUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getSysop() {
		if (empty(self::$sysop)) {
			self::$sysop = SysopManager::getInstance();
		}
		return self::$sysop;
	}
}
