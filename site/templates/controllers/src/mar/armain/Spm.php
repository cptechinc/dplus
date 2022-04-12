<?php namespace Controllers\Mar\Armain;
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use SalesPerson;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus CRUD
use Dplus\Codes\Mar\Spm as SpmManager;
// Dplus Validators
use Dplus\Filters\Mar\SalesPerson as Filter;

class Spm extends Base {
	const DPLUSPERMISSION = 'spm';
	const SHOWONPAGE = 10;

	private static $spm;

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
			$spm = self::getSpm();
			$spm->processInput(self::pw('input'));
		}

		self::pw('session')->redirect(self::redirectUrl($data), $http301 = false);
	}

	private static function salesperson($data) {
		self::sanitizeParametersShort($data, ['id|text', 'action|text']);
		self::pw('page')->headline = "SPM: Adding Salesperson";
		$spm = self::getSpm();
		$salesrep = $spm->getOrCreate($data->id);

		if ($salesrep->isNew() === false) {
			self::pw('page')->headline = "SPM: Editing $data->id";
			$spm->lockrecord($salesrep);
		}

		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/mar/spm/rep/form/.js.twig', ['spm' => $spm]);
		$html = self::displaySalesperson($data, $salesrep);
		self::getSpm()->deleteResponse();
		return $html;
	}

	private static function list($data) {
		self::sanitizeParametersShort($data, ['q|text']);
		$page = self::pw('page');
		$page->headline = "Salesperson Maintenance";
		$spm  = self::getSpm();
		$spm->recordlocker->deleteLock();

		$filter = new Filter();

		if ($data->q) {
			$page->headline = "SPM: Searching '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby($page);
		$reps = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		self::initHooks();
		$page->js .= self::pw('config')->twig->render('code-tables/mar/spm/list/.js.twig');
		$html = self::displayList($data, $reps);
		self::getSpm()->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displaySalesperson($data, SalesPerson $salesrep) {
		$spm  = self::getSpm();

		$html = '';
		$html .= self::pw('config')->twig->render('code-tables/mar/spm/bread-crumbs.twig');
		$html .= '<div class="mb-3">'.self::displayResponse($data).'</div>';
		$html .= '<div class="mb-3">'.self::displayLocked($data).'</div>';
		$html .= self::pw('config')->twig->render('code-tables/mar/spm/rep/display.twig', ['rep' => $salesrep, 'spm' => $spm]);
		return $html;
	}

	private static function displayList($data, PropelModelPager $reps) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('code-tables/mar/spm/bread-crumbs.twig');
		$html .= '<div class="mb-3">'.self::displayResponse($data).'</div>';
		$html .= $config->twig->render('code-tables/mar/spm/list/display.twig', ['spm' => self::getSpm(), 'reps' => $reps]);
		return $html;
	}

	private static function displayResponse($data) {
		$spm = self::getSpm();
		$response = $spm->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

	private static function displayLocked($data) {
		$spm = self::getSpm();

		if ($spm->recordlocker->isLocked($data->id) && $spm->recordlocker->userHasLocked($data->id) === false) {
			$msg = "Salesperson $data->id is being locked by " . $spm->recordlocker->getLockingUser($data->id);
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Salesperson $data->id is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		}
		return '';
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		return Menu::spmUrl();
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
		if (empty($focus) || self::getSpm()->exists($focus) === false) {
			return self::_repListUrl();
		}
		$filter = new Filter();
		$filter->init();
		$position = $filter->positionQuick($focus);
		$url = new Purl(self::_repListUrl());
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'spm', self::getPagenbrFromOffset($position, self::pw('session')->display));
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}

	public static function _repListUrl() {
		return self::url();
	}

	public static function redirectUrl($data) {
		if ($data->action == 'update') {
			$response = self::getSpm()->getResponse();

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

		$m->addHook('Page(pw_template=armain)::spmUrl', function($event) {
			$event->return = self::url();
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getSpm() {
		if (empty(self::$spm)) {
			self::$spm = new SpmManager();
		}
		return self::$spm;
	}
}
