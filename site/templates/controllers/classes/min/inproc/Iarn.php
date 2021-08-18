<?php namespace Controllers\Min\Inproc;

use stdClass;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use InvAdjustmentReasonQuery, InvAdjustmentReason;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Iarn
use Dplus\Min\Inproc\Iarn\Iarn as CRUDManager;
// Mvc Controllers
use Controllers\Min\Inproc\Base;

/**
 * Controller for Inventory Adjustment Reason
 */
class Iarn extends Base {
	const DPLUSPERMISSION = 'iarn';

	private static $iarn;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['id|text']);
		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
		}
		if ($data->id) {
			return self::code($data);
		}
		return self::list($data);
	}

	private static function list($data) {
		self::sanitizeParametersShort($data, ['q|text', 'orderby|text']);
		$filter = new Filters\Min\InvAdjustmentReason();

		$iarn = self::getIarn();
		$iarn->recordlocker->deleteLock();

		if ($data->q) {
			self::pw('page')->headline = "UPCX: Searching for '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby(self::pw('page'));

		if (empty($data->q) === false || empty($data->orderby) === false) {
			$sortFilter = Filters\SortFilter::fromArray(['q' => $data->q, 'orderby' => $data->orderby]);
			$sortFilter->saveToSession('iarn');
		}
		$codes = $filter->query->paginate(self::pw('input')->pageNum, 10);
		// self::pw('page')->js .= self::pw('config')->twig->render('items/iarn/list/.js.twig');
		self::initHooks();
		return self::displayList($data, $codes);
	}

	private static function code($data) {
		self::sanitizeParametersShort($data, ['id|text']);
		$iarn = self::getIarn();

		if ($iarn->exists($data->id)) {
			self::pw('page')->headline = "Inventory Adjustment Reason: $data->id";
		}

		if ($iarn->exists($data->id) === false) {
			self::pw('page')->headline = "Inventory Adjustment Reason: Creating New Reason";
		}
		$reason = $iarn->getOrCreate($data->id);

		if ($reason->isNew() === false) {
			if ($iarn->recordlocker->isLocked($reason->id) === false) {
				$iarn->recordlocker->lock($reason->id);
			}
		}
		// self::pw('page')->js .= self::pw('config')->twig->render('min/i2i/xref/form/.js.twig');
		self::initHooks();
		return self::displayCode($data, $reason);
	}


/* =============================================================
	URLs
============================================================= */
	public static function codeListUrl($focus = '') {
		if (empty($focus)) {
			return self::iarnUrl();
		}
		return self::codeListFocusUrl($focus);
	}

	public static function codeListFocusUrl($id) {
		$iarn = self::getIarn();

		if ($iarn->exists($id) === false) {
			return self::iarnUrl();
		}
		$reason = $iarn->code($id);
		$sortFilter = Filters\SortFilter::getFromSession('iarn');
		$filter = new Filters\Min\InvAdjustmentReason();

		if ($sortFilter) {
			$filter->applySortFilter($sortFilter);
		}
		$offset = $filter->position($reason);
		$pagenbr = self::getPagenbrFromOffset($offset);

		$url = new Purl(self::iarnUrl());
		$url->query->set('focus', $id);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'iarn', $pagenbr);
		if ($sortFilter) {
			if ($sortFilter->q) {
				$url->query->set('q', $sortFilter->q);
			}
			if ($sortFilter->orderby) {
				$url->query->set('orderby', $sortFilter->orderby);
			}
		}
		return $url->getUrl();
	}

	public static function codeUrl($id) {
		$url = new Purl(self::iarnUrl());
		$url->query->set('id', $id);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($id) {
		$url = new Purl(self::codeUrl($id));
		$url->query->set('action', 'delete-iarn');
		return $url->getUrl();
	}

	public static function codeNewUrl() {
		$url = new Purl(self::codeUrl('new'));
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$iarn   = self::getIarn();
		$config = self::pw('config');

		$html = '';
		$html .= self::breadCrumbsDisplay($data);
		$html .= self::responseDisplay($data);
		$html .= $config->twig->render('min/inproc/iarn/list/display.twig', ['iarn' => $iarn, 'reasons' => $codes]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $codes]);
		return $html;
	}

	private static function displayCode($data, InvAdjustmentReason $reason) {
		$config = self::pw('config');

		$html = '';
		$html .= self::breadCrumbsDisplay($data);
		$html .= self::responseDisplay($data);
		$html .= self::lockReasonDisplay($reason);
		$html .= self::pw('config')->twig->render('min/inproc/iarn/code/display.twig', ['reason' => $reason, 'iarn' => self::getIarn()]);
		return $html;
	}

	private static function responseDisplay($data) {
		$response = self::pw('session')->getFor('response', 'i2i');
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('items/itm/response-alert.twig', ['response' => $response]);
	}

	private static function breadCrumbsDisplay($data) {
		return self::pw('config')->twig->render('min/inproc/iarn/bread-crumbs.twig');
	}

	private static function lockReasonDisplay(InvAdjustmentReason $reason) {
		$config = self::pw('config');
		$iarn   = self::getIarn();
		$html = '';

		if ($iarn->recordlocker->isLocked($reason->id) === false && $iarn->recordlocker->userHasLocked($reason->id) === false) {
			$iarn->recordlocker->lock($reason->id);
		}

		if ($iarn->recordlocker->isLocked($reason->id) && $iarn->recordlocker->userHasLocked($reason->id) === false) {
			$msg = "Inventory Adjustment Reason $reason->id is being locked by " . $iarn->recordlocker->getLockingUser($reason->id);
			$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Reason Code $reason->id is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		}
		$html .= '<div class="mb-3"></div>';
		return $html;
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return Iarn CRUD Manager
	 * @return CRUDManager
	 */
	public static function getIarn() {
		if (empty(self::$iarn)) {
			self::$iarn = CRUDManager::getInstance();
		}
		return self::$iarn;
	}


/* =============================================================
	Validator, Module Getters
============================================================= */

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMin');

		$m->addHook('Page(pw_template=inproc)::codeUrl', function($event) {
			$event->return = self::codeUrl($event->arguments(0));
		});
		$m->addHook('Page(pw_template=inproc)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
		$m->addHook('Page(pw_template=inproc)::codeListUrl', function($event) {
			$event->return = self::codeListUrl($event->arguments(0));
		});

	}
}
