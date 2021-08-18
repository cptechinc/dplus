<?php namespace Controllers\Min\Inproc;

use stdClass;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
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
		self::sanitizeParametersShort($data, []);
		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
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
		return self::displayList($data, $codes);
	}


/* =============================================================
	URLs
============================================================= */


/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$iarn = self::getIarn();
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('min/inproc/iarn/list/display.twig', ['iarn' => $iarn, 'reasons' => $codes]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $codes]);
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
		// $m = self::pw('modules')->get('DpagesMin');
		//
		// $m->addHook('Page(pw_template=inproc)::subfunctionUrl', function($event) {
		// 	$event->return = self::SubfunctionUrl($event->arguments(0));
		// });

	}
}
