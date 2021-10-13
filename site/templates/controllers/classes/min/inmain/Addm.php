<?php namespace Controllers\Min\Inmain;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemAddonItemQuery, ItemAddonItem;
// ProcessWire Classes, Modules
use ProcessWire\WireData, ProcessWire\Page;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as Locker;
// Dplus Configs
use Dplus\Configs;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Min\Inmain\Addm\Addm as Manager;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Addm extends AbstractController {
	private static $addm;

	public static function index($data) {
		$fields = ['itemID|text', 'addonID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		// if (empty($data->action) === false) {
		// 	return self::handleCRUD($data);
		// }
		//
		if (empty($data->itemID) === false) {
			if (empty($data->addonID) === false) {
				return self::xref($data);
			}
		}
		return self::list($data);
	}

	private static function list($data) {
		self::sanitizeParametersShort($data, ['q|text', 'orderby|text']);
		self::pw('session')->removeFor('addm', 'sortfilter');

		$addm = self::getAddm();
		$addm->recordlocker->deleteLock();

		$filter = new Filters\Min\AddonItem();

		if ($data->q) {
			self::pw('page')->headline = "I2I: Searching for '$data->q'";
			$filter->search(strtoupper($data->q));
		}

		$filter->sortby(self::pw('page'));
		if (empty($data->q) === false || empty($data->orderby) === false) {
			$sortFilter = Filters\SortFilter::fromArray(['q' => $data->q, 'orderby' => $data->orderby]);
			$sortFilter->saveToSession('addm');
		}

		$filter->query->orderBy(ItemAddonItem::aliasproperty('itemid'), 'ASC');

		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 10);

		self::pw('page')->js .= self::pw('config')->twig->render('min/inmain/addm/list/.js.twig');
		$html = self::displayList($data, $xrefs);
		return $html;
	}

	public static function xref($data) {
		$fields = ['itemID|text', 'addonID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$addm = self::getAddm();

		$xref = $addm->getOrCreate($data->itemID, $data->addonID);

		self::pw('page')->headline = "ADDM: $data->itemID Add-on $data->addonID";

		if ($addm->exists($data->itemID, $data->addonID) === false) {
			self::pw('page')->headline = "ADDM: $data->itemID Creating Add-on";
		}

		if ($xref->isNew() === false) {
			if ($addm->recordlocker->isLocked($addm->getRecordlockerKey($xref)) === false) {
				$addm->recordlocker->lock($addm->getRecordlockerKey($xref));
			}
		}
		// self::pw('page')->js .= self::pw('config')->twig->render('min/i2i/xref/form/.js.twig');
		return self::displayXref($data, $xref);
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayList($data, PropelModelPager $xrefs) {
		$addm   = self::getAddm();
		$config = self::pw('config');

		$html  = '';
		$html .= $config->twig->render('min/inmain/addm/list/display.twig', ['addm' => $addm, 'xrefs' => $xrefs]);
		return $html;
	}

	private static function displayXref($data, ItemAddonItem $xref) {
		$addm   = self::getAddm();
		$config = self::pw('config');

		$html  = '';
		$html .= $config->twig->render('min/inmain/addm/xref/display.twig', ['addm' => $addm, 'xref' => $xref]);
		return $html;
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function addmUrl() {
		return self::pw('pages')->get('pw_template=addm')->url;
	}

	public static function xrefListUrl($focus = '') {
		if (empty($focus)) {
			return self::addmUrl();
		}
		$url = new Purl(self::addmUrl());
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}

	public static function xrefUrl($itemID, $addonID) {
		$url = new Purl(self::addmUrl());
		$url->query->set('itemID', $itemID);
		$url->query->set('addonID', $addonID);
		return $url->getUrl();
	}

	public static function xrefDeleteUrl($itemID, $addonID) {
		$url = new Purl(self::xrefUrl($itemID, $addonID));
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

	public static function xrefNewUrl() {
		return self::xrefUrl('new', 'new');
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return Addm CRUD Manager
	 * @return Manager
	 */
	public static function getAddm() {
		if (empty(self::$addm)) {
			self::$addm = Manager::getInstance();
		}
		return self::$addm;
	}

	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMin');

		$m->addHook('Page(pw_template=addm)::xrefUrl', function($event) {
			$event->return = self::xrefUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=addm)::xrefDeleteUrl', function($event) {
			$event->return = self::xrefDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=addm)::xrefNewUrl', function($event) {
			$event->return = self::xrefNewUrl();
		});

		$m->addHook('Page(pw_template=addm)::xrefListUrl', function($event) {
			$event->return = self::xrefListUrl($event->arguments(0));
		});

		// $m->addHook('Page(pw_template=addm)::xrefListFocusUrl', function($event) {
		// 	$event->return = self::xrefListFocusUrl($event->arguments(0), $event->arguments(1));
		// });
	}
}
