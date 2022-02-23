<?php namespace Controllers\Min\Inmain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use InvGroupCode;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Min\Igm as IgmManager;
// Mvc Controllers
use Controllers\Min\Base;

class Igm extends Base {
	const DPLUSPERMISSION = 'igm';
	const SHOWONPAGE = 10;

	private static $igm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = 'Inventory Group Code';

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->code) === false) {
			return self::code($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::igmUrl();
		$igm = self::getIgm();

		if ($data->action) {
			$igm->processInput(self::pw('input'));
			$url = self::igmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		self::getIgm()->recordlocker->deleteLock();
		$page   = self::pw('page');
		$page->headline = "Inventory Group Code";

		$filter = new Filters\Min\InvGroupCode();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "IGM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/min/igm/list/.js.twig');
		$html = self::displayList($data, $codes);
		self::getIgm()->deleteResponse();
		return $html;
	}

	private static function code($data) {
		self::pw('page')->headline = "IGM: Adding New Code";

		$igm = self::getIgm();
		$invGroup = $igm->getOrCreate($data->code);

		if ($invGroup->isNew() === false) {
			self::pw('page')->headline = "IGM: Editing $data->code";
			$igm->lockrecord($invGroup);
		}
		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/min/igm/edit/.js.twig', ['igm' => $igm]);
		return self::displayCode($data, $invGroup);
	}

/* =============================================================
	URLs
============================================================= */
	public static function igmUrl($code = '') {
		if (empty($code)) {
			return Menu::igmUrl();
		}
		return self::igmFocusUrl($code);
	}

	public static function igmFocusUrl($focus) {
		$filter = new Filters\Min\InvGroupCode();
		if ($filter->exists($focus) === false) {
			return Menu::igmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::igmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'igm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeEditUrl($code) {
		$url = new Purl(Menu::igmUrl());
		$url->query->set('code', $code);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(self::codeEditUrl($code));
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$igm = self::getIgm();

		$html  = '';
		$html .= $config->twig->render('code-tables/min/igm/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/min/igm/list.twig', ['manager' => $igm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $igm]);
		return $html;
	}

	private static function displayCode($data, InvGroupCode $invGroup) {
		$config = self::pw('config');
		$igm = self::getIgm();
		$igm->initFieldAttributes();

		$html  = '';
		$html .= $config->twig->render('code-tables/min/igm/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= self::displayLocked($data);
		$html .= $config->twig->render('code-tables/min/igm/edit/display.twig', ['igm' => $igm, 'invgroup' => $invGroup]);
		return $html;
	}

	private static function displayResponse($data) {
		$igm = self::getIgm();
		$response = $igm->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

	private static function displayLocked($data) {
		$igm = self::getIgm();

		if ($igm->recordlocker->isLocked($data->code) && $igm->recordlocker->userHasLocked($data->code) === false) {
			$msg = "Group Code $data->code is being locked by " . $igm->recordlocker->getLockingUser($data->code);
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Code $data->code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		}
		return '';
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

		$m->addHook('Page(pw_template=inmain)::codeListUrl', function($event) {
			$event->return = self::igmUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=inmain)::codeAddUrl', function($event) {
			$event->return = self::codeEditUrl('new');
		});

		$m->addHook('Page(pw_template=inmain)::codeEditUrl', function($event) {
			$event->return = self::codeEditUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=inmain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getIgm() {
		if (empty(self::$igm)) {
			self::$igm = new IgmManager();
		}
		return self::$igm;
	}
}
