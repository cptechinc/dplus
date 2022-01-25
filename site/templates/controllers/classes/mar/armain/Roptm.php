<?php namespace Controllers\Mar\Armain;
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use SalesPerson;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Roptm as RoptmManager;
// Dplus Validators
use Dplus\Filters;
// Dplus Codes
use Dplus\Codes;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Roptm extends Controller {
	private static $roptm;

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
		$salesrep = self::getRoptm()->get_create($data->id);
		self::pw('page')->js .= self::pw('config')->twig->render('mar/armain/roptm/form/.js.twig');
		return self::salespersonDisplay($data, $salesrep);
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$page = self::pw('page');
		// $roptm  = self::getRoptm();
		// $roptm->recordlocker->deleteLock();

		$filter = new Filters\Msa\MsaSysopCode();
		$filter->system('IN');

		if ($data->q) {
			$page->headline = "ROPTM: Searching '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		// $page->js .= self::pw('config')->twig->render('mar/armain/roptm/list/.js.twig');
		return self::displayList($data, $codes);
	}

/* =============================================================
	CRUD
============================================================= */
	public static function handleCRUD($data) {
		$fields = ['action|text', 'id|text'];
		self::sanitizeParameters($data, $fields);

		if ($data->action) {
			$roptm = self::pw('modules')->get('Roptm');
			$roptm->process_input(self::pw('input'));
		}

		self::pw('session')->redirect(self::redirectUrl($data), $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	private static function salespersonDisplay($data, SalesPerson $salesrep) {
		$roptm  = self::getRoptm();
		$html =  self::lockUser($roptm, $salesrep);
		$html .= self::pw('config')->twig->render('mar/armain/roptm/form/page.twig', ['person' => $salesrep, 'roptm' => $roptm]);
		return $html;
	}

	private static function lockUser(RoptmManager $roptm, SalesPerson $person) {
		$roptm = self::getRoptm();
		$html = '';

		if ($roptm->recordlocker->isLocked($person->id) && $roptm->recordlocker->userHasLocked($person->id) === false) {
			$msg = "Sales Person $person->id is being locked by " . $roptm->recordlocker->getLockingUser($person->id);
			$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Saless Person $person->id is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			$html .= '<div class="mb-3"></div>';
		} elseif ($roptm->recordlocker->isLocked($person->id) === false) {
			$roptm->recordlocker->lock($person->id);
		}
		return $html;
	}

	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('mar/armain/roptm/list/page.twig', ['sysop' => self::getSysop(), 'codes' => $codes]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $codes]);
		return $html;
	}

/* =============================================================
	Classes, Module Getters
============================================================= */
	public static function getRoptm() {
		if (empty(self::$roptm)) {
			self::$roptm = self::pw('modules')->get('Roptm');
		}
		return self::$roptm;
	}

	public static function getSysop() {
		return Codes\Msa\Sysop::getInstance();
	}

/* =============================================================
	URLs
============================================================= */
	public static function codeUrl($id) {
		$url = new Purl(self::url());
		$url->query->set('code', $id);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($id) {
		$url = new Purl(self::codeUrl($id));
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

	public static function codeAddUrl() {
		return self::codeUrl('new');
	}

	public static function codeListUrl($focus = '') {
		if (empty($focus) || self::getRoptm()->exists($focus) === false) {
			return self::url();
		}
		$filter = new FilterSalesPerson();
		$filter->init();
		$position = $filter->positionQuick($focus);
		$url = new Purl(self::_repListUrl());
		$url = self::pw('modules')->get('Dpurl')->paginate($url, self::pw('pages')->get('pw_template=roptm')->name, self::getPagenbrFromOffset($position, self::pw('session')->display));
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}


	public static function url() {
		return self::pw('pages')->get('pw_template=roptm')->url;
	}

	public static function redirectUrl($data) {
		if ($data->action == 'update') {
			$response = self::pw('session')->getFor('response', 'roptm');

			if ($response) {
				if ($response->has_success()) {
					return self::codeListUrl($data->id);
				}
				return self::codeUrl($data->id);
			}
		}
		return self::repListUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMar');

		$m->addHook('Page(pw_template=roptm)::codeEditUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::codeUrl($id);
		});

		$m->addHook('Page(pw_template=roptm)::codeDeleteUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::codeDeleteUrl($id);
		});

		$m->addHook('Page(pw_template=roptm)::codeAddUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::codeAddUrl();
		});

		$m->addHook('Page(pw_template=roptm)::codeListUrl', function($event) {
			$id = $event->arguments(0);
			$event->return = self::codeListUrl($id);
		});

		$m->addHook('Page(pw_template=roptm)::roptmUrl', function($event) {
			$event->return = self::url();
		});
	}
}
