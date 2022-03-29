<?php namespace Controllers\Mar\Armain;
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ArTermsCode;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus CRUD
use Dplus\Codes\Mar\Trm as TrmManager;
// Dplus Validators
use Dplus\Filters\Mar\ArTermsCode as Filter;

class Trm extends Base {
	const DPLUSPERMISSION = 'trm';
	const SHOWONPAGE = 10;

	private static $trm;

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
			return self::salesperson($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'id|text'];
		self::sanitizeParameters($data, $fields);

		if ($data->action) {
			$trm = self::getTrm();
			$trm->processInput(self::pw('input'));
		}

		self::pw('session')->redirect(self::redirectUrl($data), $http301 = false);
	}

	private static function salesperson($data) {
		self::sanitizeParametersShort($data, ['id|text', 'action|text']);
		self::pw('page')->headline = "TRM: Adding Salesperson";
		$trm = self::getTrm();
		$code = $trm->getOrCreate($data->id);

		if ($code->isNew() === false) {
			self::pw('page')->headline = "TRM: Editing $data->id";
			$trm->lockrecord($code);
		}

		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/mar/trm/rep/form/.js.twig', ['trm' => $trm]);
		$html = self::displaySalesperson($data, $code);
		self::getTrm()->deleteResponse();
		return $html;
	}

	private static function list($data) {
		self::sanitizeParametersShort($data, ['q|text']);
		$page = self::pw('page');
		$page->headline = "Salesperson Maintenance";
		$trm  = self::getTrm();
		$trm->recordlocker->deleteLock();

		$filter = new Filter();

		if ($data->q) {
			$page->headline = "TRM: Searching '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby($page);
		$reps = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		self::initHooks();
		$page->js .= self::pw('config')->twig->render('code-tables/mar/trm/list/.js.twig');
		$html = self::displayList($data, $reps);
		self::getTrm()->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displaySalesperson($data, ArTermsCode $code) {
		$trm  = self::getTrm();

		$html = '';
		$html .= self::pw('config')->twig->render('code-tables/mar/trm/bread-crumbs.twig');
		$html .= '<div class="mb-3">'.self::displayResponse($data).'</div>';
		$html .= '<div class="mb-3">'.self::displayLocked($data).'</div>';
		$html .= self::pw('config')->twig->render('code-tables/mar/trm/rep/display.twig', ['rep' => $code, 'trm' => $trm]);
		return $html;
	}

	private static function displayList($data, PropelModelPager $reps) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('code-tables/mar/trm/bread-crumbs.twig');
		$html .= '<div class="mb-3">'.self::displayResponse($data).'</div>';
		$html .= $config->twig->render('code-tables/mar/trm/list/display.twig', ['trm' => self::getTrm(), 'reps' => $reps]);
		return $html;
	}

	private static function displayResponse($data) {
		$trm = self::getTrm();
		$response = $trm->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

	private static function displayLocked($data) {
		$trm = self::getTrm();

		if ($trm->recordlocker->isLocked($data->id) && $trm->recordlocker->userHasLocked($data->id) === false) {
			$msg = "Salesperson $data->id is being locked by " . $trm->recordlocker->getLockingUser($data->id);
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Salesperson $data->id is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		}
		return '';
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		return Menu::trmUrl();
	}

	public static function repUrl($id) {
		$url = new Purl(self::url());
		$url->query->set('id', $id);
		return $url->getUrl();
	}

	public static function repDeleteUrl($id) {
		$url = new Purl(self::repUrl($id));
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

	public static function repAddUrl() {
		return self::repUrl('new');
	}

	public static function repListUrl($focus = '') {
		if (empty($focus) || self::getTrm()->exists($focus) === false) {
			return self::_repListUrl();
		}
		$filter = new Filter();
		$filter->init();
		$position = $filter->positionQuick($focus);
		$url = new Purl(self::_repListUrl());
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'trm', self::getPagenbrFromOffset($position, self::pw('session')->display));
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}

	public static function _repListUrl() {
		return self::url();
	}

	public static function redirectUrl($data) {
		if ($data->action == 'update') {
			$response = self::getTrm()->getResponse();

			if ($response) {
				if ($response->hasSuccess()) {
					return self::repListUrl($data->id);
				}
				return self::repUrl($data->id);
			}
		}
		return self::repListUrl();
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

		$m->addHook('Page(pw_template=armain)::repEditUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::repUrl($id);
		});

		$m->addHook('Page(pw_template=armain)::repDeleteUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::repDeleteUrl($id);
		});

		$m->addHook('Page(pw_template=armain)::repAddUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::repAddUrl();
		});

		$m->addHook('Page(pw_template=armain)::repListUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::repListUrl($id);
		});

		$m->addHook('Page(pw_template=armain)::trmUrl', function($event) {
			$event->return = self::url();
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getTrm() {
		if (empty(self::$trm)) {
			self::$trm = new TrmManager();
		}
		return self::$trm;
	}
}
