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

	public static function handleCRUD($data) {
		$fields = ['itemID|text', 'addonID|text', 'action|text'];
		self::sanitizeParameters($data, $fields);
		$addm = self::getAddm();
		$addm->processInput(self::pw('input'));
		$url = self::xrefListUrl();

		switch ($data->action) {
			case 'update':
				if ($addm->exists($data->itemID, $data->addonID)) {
					$xref = $addm->xref($data->itemID, $data->addonID);
					$url = self::xrefListFocusUrl($addm->getRecordlockerKey($xref));
				}
				break;
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		self::sanitizeParametersShort($data, ['q|text', 'orderby|text']);
		self::pw('session')->removeFor('addm', 'sortfilter');

		$addm = self::getAddm();
		$addm->recordlocker->deleteLock();

		$filter = new Filters\Min\AddonItem();

		if ($data->q) {
			self::pw('page')->headline = "Addm: Searching for '$data->q'";
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
			self::pw('page')->headline = "ADDM: Creating Add-on";
		}

		if ($xref->isNew() === false) {
			if ($addm->recordlocker->isLocked($addm->getRecordlockerKey($xref)) === false) {
				$addm->recordlocker->lock($addm->getRecordlockerKey($xref));
			}
		}
		self::pw('page')->js .= self::pw('config')->twig->render('min/inmain/addm/xref/form/.js.twig');
		return self::displayXref($data, $xref);
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayList($data, PropelModelPager $xrefs) {
		$addm   = self::getAddm();
		$config = self::pw('config');

		$html  = '';
		$html .= self::displayResponse();
		$html .= $config->twig->render('min/inmain/addm/list/display.twig', ['addm' => $addm, 'xrefs' => $xrefs]);
		return $html;
	}

	private static function displayXref($data, ItemAddonItem $xref) {
		$addm   = self::getAddm();
		$config = self::pw('config');

		$html  = '';
		$html .= self::displayResponse();
		$html .= $config->twig->render('min/inmain/addm/xref/display.twig', ['addm' => $addm, 'xref' => $xref]);
		return $html;
	}

	// NOTE: Keep public for Itm
	public static function displayResponse() {
		$response = self::getAddm()->getResponse();

		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('items/itm/response-alert-new.twig', ['response' => $response]);
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
		return self::xrefListFocusUrl($focus);
	}

	public static function xrefListFocusUrl($focus) {
		$addm = self::getAddm();

		if ($addm->existsFromRecordlockerKey($focus) === false) {
			return self::addmUrl();
		}

		$xref = $addm->xrefFromRecordlockerKey($focus);
		$filter     = new Filters\Min\AddonItem();
		$sortFilter = Filters\SortFilter::getFromSession('addm');

		if ($sortFilter) {
			$filter->applySortFilter($sortFilter);
		}
		$filter->query->orderBy(ItemAddonItem::aliasproperty('itemid'), 'ASC');
		$offset  = $filter->positionQuick($xref->itemid);
		$pagenbr = self::getPagenbrFromOffset($offset);

		$url = new Purl(self::addmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'addm', $pagenbr);
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
