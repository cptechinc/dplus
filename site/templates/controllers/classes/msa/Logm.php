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
use Dplus\Msa;
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
		$logm = self::getLogm();

		if ($data->action) {
			$logm->processInput(self::pw('input'));
			$url  = self::logmUrl($data->id);
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
		$ids = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);

		self::initHooks();
		$page->js .= self::pw('config')->twig->render('msa/logm/list/.js.twig');
		$html = self::displayList($data, $ids);
		self::getLogm()->deleteResponse();
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

		if ($user->isNew() === false) {
			$logm->lockrecord($data->id);
		}
		self::initHooks();
		$page->js .= self::pw('config')->twig->render('msa/logm/user/.js.twig', ['logm' => self::getLogm()]);
		$html = self::displayUser($data, $user);
		self::getLogm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function logmUrl($id = '') {
		if (empty($id)) {
			return Menu::logmUrl();
		}
		return self::logmFocusUrl($id);
	}

	public static function logmFocusUrl($focus) {
		$filter = new Filters\Msa\DplusUser();
		if ($filter->exists($focus) === false) {
			return Menu::logmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(self::logmUrl());
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

	public static function userEditContactUrl($id) {
		$url = new Purl(self::userEditUrl($id));
		$url->path->add('contact');
		return $url->getUrl();
	}

	public static function userEditPasswordUrl($id) {
		$url = new Purl(self::userEditUrl($id));
		$url->path->add('password');
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
		$html .= self::displayResponse($data);
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

	public static function displayLock($data) {
		$logm = self::getLogm();

		if ($logm->recordlocker->isLocked($data->id) && $logm->recordlocker->userHasLocked($data->id) === false) {
			$msg = "User $data->id is being locked by " . $logm->recordlocker->getLockingUser($data->id);
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "User is Locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		}
		return '';
	}

	private static function displayUser($data, DplusUser $user) {
		$config = self::pw('config');
		$logm   = self::getLogm();

		$html  = '';
		$html .= '<div class="mb-3">' . self::displayLock($data) . '</div>';
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('msa/logm/user.twig', ['logm' => $logm, 'duser' => $user]);
		$html .= $config->twig->render('msa/logm/user/password/modal/pswd.twig', ['logm' => $logm, 'duser' => $user]);
		$html .= $config->twig->render('msa/logm/user/password/modal/pswd-web.twig', ['logm' => $logm, 'duser' => $user]);
		return $html;
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

		$m->addHook('Page(pw_template=msa)::logmUrl', function($event) {
			$event->return = self::logmUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=msa)::userEditUrl', function($event) {
			$event->return = self::userEditUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=msa)::userEditContactUrl', function($event) {
			$event->return = self::userEditContactUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=msa)::userDeleteUrl', function($event) {
			$event->return = self::userDeleteUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=msa)::userEditPasswordUrl', function($event) {
			$event->return = self::userEditPasswordUrl($event->arguments(0));
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
