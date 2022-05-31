<?php namespace Controllers\Mar;
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use SalesPerson;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Spm as SpmManager;
// Dplus Validators
use Dplus\Filters\Mar\SalesPerson as FilterSalesPerson;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Spm extends Controller {
	private static $spm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['id|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->id) === false) {
			return self::salesperson($data);
		}
		return self::list($data);
	}

	public static function salesperson($data) {
		$data = self::sanitizeParametersShort($data, ['id|text', 'action|text']);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$salesrep = self::getSpm()->get_create($data->id);
		self::pw('page')->js .= self::pw('config')->twig->render('mar/armain/spm/form/.js.twig');
		return self::salespersonDisplay($data, $salesrep);
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$page = self::pw('page');
		$spm  = self::getSpm();
		$spm->recordlocker->deleteLock();

		$filter = new FilterSalesPerson();
		$filter->init();

		if ($data->q) {
			$page->headline = "SPM: Searching '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby($page);
		$reps = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$page->js .= self::pw('config')->twig->render('mar/armain/spm/list/.js.twig');
		return self::listDisplay($data, $reps);
	}

/* =============================================================
	CRUD
============================================================= */
	public static function handleCRUD($data) {
		$fields = ['action|text', 'id|text'];
		self::sanitizeParameters($data, $fields);

		if ($data->action) {
			$spm = self::pw('modules')->get('Spm');
			$spm->process_input(self::pw('input'));
		}

		self::pw('session')->redirect(self::redirectUrl($data), $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	private static function salespersonDisplay($data, SalesPerson $salesrep) {
		$spm  = self::getSpm();
		$html =  self::lockUser($spm, $salesrep);
		$html .= self::pw('config')->twig->render('mar/armain/spm/form/page.twig', ['person' => $salesrep, 'spm' => $spm]);
		return $html;
	}

	private static function lockUser(SpmManager $spm, SalesPerson $person) {
		$spm = self::getSpm();
		$html = '';

		if ($spm->recordlocker->isLocked($person->id) && $spm->recordlocker->userHasLocked($person->id) === false) {
			$msg = "Sales Person $person->id is being locked by " . $spm->recordlocker->getLockingUser($person->id);
			$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Saless Person $person->id is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			$html .= '<div class="mb-3"></div>';
		} elseif ($spm->recordlocker->isLocked($person->id) === false) {
			$spm->recordlocker->lock($person->id);
		}
		return $html;
	}

	private static function listDisplay($data, PropelModelPager $reps) {
		$config = self::pw('config');
		$html = '';
		$html .= $config->twig->render('mar/armain/spm/list/page.twig', ['spm' => self::pw('modules')->get('Spm'), 'people' => $reps]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $reps]);
		return $html;
	}

/* =============================================================
	Classes, Module Getters
============================================================= */
	public static function getSpm() {
		if (empty(self::$spm)) {
			self::$spm = self::pw('modules')->get('Spm');
		}
		return self::$spm;
	}

/* =============================================================
	URLs
============================================================= */
	public static function repUrl($id) {
		$url = new Purl(self::pw('pages')->get('pw_template=spm')->url);
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
		$filter = new FilterSalesPerson();
		$filter->init();
		$position = $filter->positionQuick($focus);
		$url = new Purl(self::_repListUrl());
		$url = self::pw('modules')->get('Dpurl')->paginate($url, self::pw('pages')->get('pw_template=spm')->name, self::getPagenbrFromOffset($position, self::pw('session')->display));
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}

	public static function _repListUrl() {
		return self::spmUrl();
	}

	public static function spmUrl() {
		return self::pw('pages')->get('pw_template=spm')->url;
	}

	public static function redirectUrl($data) {
		if ($data->action == 'update') {
			$response = self::pw('session')->getFor('response', 'spm');

			if ($response) {
				if ($response->has_success()) {
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
	public static function init() {
		$m = self::pw('modules')->get('DpagesMar');

		$m->addHook('Page(pw_template=spm)::repEditUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::repUrl($id);
		});

		$m->addHook('Page(pw_template=spm)::repDeleteUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::repDeleteUrl($id);
		});

		$m->addHook('Page(pw_template=spm)::repAddUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::repAddUrl();
		});

		$m->addHook('Page(pw_template=spm)::repListUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::repListUrl($id);
		});

		$m->addHook('Page(pw_template=spm)::spmUrl', function($event) {
			$event->return = self::spmUrl();
		});
	}
}
