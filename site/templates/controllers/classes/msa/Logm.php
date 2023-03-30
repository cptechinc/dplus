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
use ProcessWire\WireData;

class Logm extends Base {
	const DPLUSPERMISSION = 'logm';
	const SHOWONPAGE = 10;
	const TITLE   = 'Login ID Entry';
	const SUMMARY = 'View / Edit Logins';

/* =============================================================
	1. Indexes
============================================================= */
	public static function index($data) {
		$fields = ['id|string', 'action|text'];
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
		$fields = ['id|string', 'action|text'];
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
		self::pw('page')->headline = "Login ID Entry";

		self::initHooks();
		$html  = self::displayList($data, self::getList($data));
		self::getLogm()->deleteResponse();
		return $html;
	}

	private static function user(WireData $data) {
		self::pw('page')->headline = "Login ID Entry Edit";
		$logm = self::getLogm();
		$user = $logm->getOrCreate($data->id);

		if ($user->isNew() === false) {
			$logm->lockrecord($data->id);
		}
		self::initHooks();
		self::appendJsUser($data);
		$html = self::displayUser($data, $user);
		self::getLogm()->deleteResponse();
		return $html;
	}

/* =============================================================
	3. Data Fetching / Requests / Retrieval
============================================================= */
	/**
	 * Return List of Users filtered by Query Parameters
	 * @param  WireData $data
	 * @return PropelModelPager
	 */
	private static function getList(WireData $data) {
		self::sanitizeParametersShort($data, ['q|text', 'col|text']);

		$filter = new Filters\Msa\DplusUser();

		if (strlen($data->q) > 0) {
			$cols = self::pw('sanitizer')->array($data->col, ['delimiter' => ',']);
			$cols = empty(array_filter($cols)) ? ['id', 'name'] : $cols;
			$filter->search($data->q, $cols);
		}
		$filter->sort(self::pw('input')->get);
		return $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
	}

/* =============================================================
	4. URLs
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
	5. Displays
============================================================= */
	private static function displayList(WireData $data, PropelModelPager $users) {
		$html  = self::renderBreadcrumbs($data);
		$html .= self::renderResponse($data);
		$html .= $data->has('print') ? self::renderListForPrinting($data, $users) : self::renderList($data, $users);
		return $html;
	}

	private static function displayUser($data, DplusUser $user) {
		$html  = self::renderBreadcrumbs($data);
		$html .= '<div class="mb-3">' . self::renderLock($data) . '</div>';
		$html .= self::renderResponse($data);
		$html .= self::renderUser($data, $user);
		return $html;
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	// NOTE: keep protected so Logm\Contact can use
	protected static function renderBreadcrumbs(WireData $data) {
		return self::pw('config')->twig->render('msa/logm/bread-crumbs.twig');
	}

	private static function renderResponse(WireData $data) {
		$logm = self::getLogm();
		$response = $logm->getResponse();
		if (empty($response) || $response->hasSuccess()) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

	// NOTE: keep protected so Logm\Contact can use
	protected static function renderLock($data) {
		$logm = self::getLogm();

		if ($logm->recordlocker->isLocked($data->id) === false || $logm->recordlocker->userHasLocked($data->id)) {
			return '';
		}
		$msg = "User $data->id is being locked by " . $logm->recordlocker->getLockingUser($data->id);
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "User is Locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
	}

	private static function renderList(WireData $data, PropelModelPager $users) {
		$logm = self::getLogm();
		return self::pw('config')->twig->render('msa/logm/list/display.twig', ['logm' => $logm, 'users' => $users]);
	}

	private static function renderListForPrinting(WireData $data, PropelModelPager $users) {
		return self::pw('config')->twig->render('msa/logm/list/display-print.twig', ['users' => $users]);
	}

	private static function renderUser(WireData $data, DplusUser $user) {
		$logm = self::getLogm();
		return self::pw('config')->twig->render('msa/logm/user/display.twig', ['logm' => $logm, 'duser' => $user]);
	}

/* =============================================================
	7. Class / Module Getters
============================================================= */
	public static function getLogm() {
		return LogmManager::getInstance();
	}

/* =============================================================
	7. Supplemental
============================================================= */
	private static function appendJsUser(WireData $data) {
		$jsPath = self::getJsPath($data);

		$scripts = [
			'classes/Alerts.js', 'classes/Requests.js', 'classes/Inputs.js', 'classes/Form.js', 
			'validate-form.js', 'events.js',
			'contact/validate-form.js', 'contact/events.js'
		];

		foreach ($scripts as $script) {
			if (file_exists(self::pw('config')->paths->templates . $jsPath . $script)) {
				self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl($jsPath . $script));
			}
		}
	}	

	private static function getJsPath(WireData $data) {
		return 'scripts/pages/' . self::getNamespaceAsPath() . '/' . self::getClassNameAsPath() . '/';
	}

/* =============================================================
	9. Hooks / Object Decorating
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

		$m->addHook('Page(pw_template=msa)::userAddUrl', function($event) {
			$event->return = self::userEditUrl('new');
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

		$m->addHook('Page(pw_template=msa)::logmContactUrl', function($event) {
			$event->return = self::userEditContactUrl($event->arguments(0));
		});
	}

}
