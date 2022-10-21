<?php namespace Controllers\Min\Inmain;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use  ItemXrefUpc;
// ProcessWire Classes, Modules
use ProcessWire\WireData, ProcessWire\Page, ProcessWire\XrefUpc as UpcCRUD;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as Locker;
// Dplus Configs
use Dplus\Configs;
// Dplus Filters
use Dplus\Filters;
// Dplus X-Refs
use Dplus\Xrefs;

class Upcx extends AbstractController {
	const DPLUSPERMISSION = 'upcx';
	const TITLE = 'UPC Item X-Ref';
	const SUMMARY = 'View / Edit UPC Item X-Refs';
	const SHOWONPAGE      = 10;
	private static $upcx;

/* =============================================================
	Index Functions
============================================================= */
	public static function index($data) {
		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}
		// Sanitize Params, parse route from params
		$fields = ['upc|text', 'itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->upc) === false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateUserPermission() === false) {
			return self::pw('session')->redirect(self::url(), $http301 = false);
		}
		$fields = ['action|text', 'upc|text', 'itemID|text',];
		self::sanitizeParameters($data, $fields);

		if (empty($data->action) === false) {
			$upcx = self::getUpcx();
			$upcx->processInput(self::pw('input'));

			switch($data->action) {
				case 'delete':
					self::pw('session')->redirect(self::xrefListUrl(), $http301 = false);
					break;
				case 'update':
					// self::pw('session')->redirect(self::xrefListUrl(implode(Locker::glue(), [$data->upc, $data->itemID])), $http301 = false);
					self::pw('session')->redirect(self::xrefUrl($data->upc, $data->itemID), $http301 = false);
					break;
				default:
					self::pw('session')->redirect(self::xrefUrl($data->upc, $data->itemID), $http301 = false);
					break;
			}
		}
		self::pw('session')->redirect(self::xrefUrl($data->upc, $data->itemID), $http301 = false);
	}

	private static function xref(WireData $data) {
		self::sanitizeParametersShort($data, ['upc|text', 'itemID|text', 'action|text']);
		self::pw('page')->show_breadcrumbs = false;

		$upcx = self::getUpcx();
		$xref = $upcx->getOrCreateXref($data->upc, $data->itemID);
		$page = self::pw('page');
		$page->headline = "UPCX: Create X-Ref";

		if ($xref->isNew() === false) {
			$upcx->lockrecord($xref);
			$page->headline = "UPCX: $xref->upc";
		}
		
		self::initHooks();
		$page->js   .= self::renderXrefJs($data);
		$html = self::displayXref($data, $xref);
		$upcx->deleteResponse();
		return $html;
	}

	private static function list(WireData $data) {
		self::sanitizeParametersShort($data, ['q|text', 'orderby|text']);
		self::getUpcx()->recordlocker->deleteLock();
		self::setupSortfilter($data);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = self::TITLE;

		$xrefs = self::getXrefPaginatedList($data);

		self::initHooks();
		self::pw('page')->js .= self::renderListJs($data);
		$html = self::displayList($data, $xrefs);
		self::getUpcx()->deleteResponse();
		return $html;
	}

/* =============================================================
	X-ref List Retrival Functions
============================================================= */
	private static function getXrefPaginatedList(WireData $data) {
		$filter = new Filters\Min\Upcx();

		if ($data->q) {
			$filter->search(strtoupper($data->q));
		}
		$filter->sort(self::pw('input')->get);
		$filter->query->orderBy(ItemXrefUpc::aliasproperty('upc'), 'ASC');
		return $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayList(WireData $data, PropelModelPager $xrefs) {
		$html = '';
		$html .= self::renderBreadcrumbs();
		$html .= self::renderResponse();
		$html .= self::renderList($data, $xrefs);
		return $html;
	}

	private static function displayXref(WireData $data, ItemXrefUpc $xref) {
		$html = '';
		$html .= self::renderBreadcrumbs();
		$html .= self::renderXrefIsLockedAlert($xref);
		$html .= self::renderResponse();
		$html .= self::renderXref($data, $xref);
		return $html;
	}

/* =============================================================
	Render HTML
============================================================= */
	private static function renderBreadcrumbs() {
		return self::pw('config')->twig->render('items/upcx/.new/bread-crumbs.twig', ['upcx' => self::getUpcx()]);
	}

	/**	NOTE: Keep public for ITM */
	public static function renderResponse() {
		$response = self::getUpcx()->getResponse();

		if (empty($response) || $response->hasError() === false) {
			return '';
		}
		return self::pw('config')->twig->render('items/cxm/.new/response.twig', ['response' => $response]);
	}

	private static function renderList(WireData $data, PropelModelPager $xrefs) {
		$upcx = self::getUpcx();
		return self::pw('config')->twig->render('items/upcx/.new/list/display.twig', ['upcx' => $upcx, 'xrefs' => $xrefs]);
	}

	private static function renderListJs(WireData $data) {
		return self::pw('config')->twig->render('items/upcx/.new/list/.js.twig');
	}

	private static function renderXrefJs(WireData $data) {
		$configs = new WireData();
		$configs->in = Configs\In::config();
		return self::pw('config')->twig->render('items/upcx/form/js.twig', ['configs' => $configs]);
	}

	/** NOTE: Keep public for ITM */
	public static function renderXrefIsLockedAlert(ItemXrefUpc $xref) {
		if ($xref->isNew()) {
			return '';
		}

		$upcx = self::getUpcx();
		if ($upcx->lockrecord($xref)) {
			return '';
		}
		$key = $upcx->getRecordlockerKey($xref);

		$msg = "CXM $xref->upc is being locked by " . $upcx->recordlocker->getLockingUser($key);
		$alert = self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "CXM $xref->upc is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		$html  = '<div class="mb-3">' . $alert . '</div>';
		return $html;
	}

	private static function renderXref(WireData $data, ItemXrefUpc $xref) {
		return self::pw('config')->twig->render('items/upcx/.new/xref/display.twig', ['upcx' => self::getUpcx(), 'xref' => $xref]);
	}


/* =============================================================
	URL Functions
============================================================= */
	/**
	 * Return URL to UPCX
	 * @return string
	 */
	public static function url() {
		return Menu::upcxUrl();
	}

	/**
	 * Return URL to view / edit UPC
	 * @param  string $upc    UPC Code
	 * @param  string $itemID Item ID
	 * @return string
	 */
	public static function xrefUrl($upc, $itemID = '') {
		$url = new Purl(self::url());
		$url->query->set('upc', $upc);

		if ($itemID) {
			$url->query->set('itemID', $itemID);
		}
		return $url->getUrl();
	}

	/**
	 * Return URL to List the UPCs associated with the ItemID
	 * @param  string $itemID Item ID
	 * @return string
	 */
	public static function xrefListUrl($focus = '') {
		if ($focus == '') {
			return self::url();
		}
		return self::xrefListFocusUrl($focus);
	}

	/**
	 * Return UPCX List Url
	 * @param  string $focus UPC to focus on
	 * @return string
	 */
	public static function xrefListFocusUrl($focus = '') {
		$upcx = self::getUpcx();

		if ($focus == '' || $upcx->existsByKey($focus) === false) {
			return self::url();
		}

		$sortFilter = Filters\SortFilter::getFromSession('upcx');
		$xref   = $upcx->xrefByKey($focus);
		$filter = new Filters\Min\Upcx();

		if ($sortFilter) {
			$filter->applySortFilter($sortFilter);
		}

		$filter->query->orderBy(ItemXrefUpc::aliasproperty('upc'), 'ASC');
		$offset = $filter->position($xref);
		$pagenbr = self::getPagenbrFromOffset($offset);

		$url = new Purl(self::url());
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'upcx', $pagenbr);
		$url->query->set('focus', $focus);

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

	/**
	 * Return URL to delete UPC
	 * @param  string $upc    UPC Code
	 * @param  string $itemID Item ID
	 * @return string
	 */
	public static function xrefDeleteUrl($upc, $itemID) {
		$url = new Purl(self::url());
		$url->query->set('action', 'delete');
		$url->query->set('upc', $upc);
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	public static function getUpcx() {
		return Xrefs\Upcx::instance();
	}

	private static function setupSortfilter(WireData $data) {
		self::pw('session')->removeFor('upcx', 'sortfilter');

		if (empty($data->q) || empty($data->orderby)) {
			return false;
		}

		$sortFilter = Filters\SortFilter::fromArray(['q' => $data->q, 'orderby' => $data->orderby]);
		$sortFilter->saveToSession('upcx');
		return true;
	}

/* =============================================================
	Hook Functions
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=inmain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=inmain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=inmain)::xrefUrl', function($event) {
			$event->return = self::xrefUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=inmain)::xrefListUrl', function($event) {
			$event->return = self::xrefListUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=inmain)::xrefCreateUrl', function($event) {
			$event->return = self::xrefUrl('new');
		});

		$m->addHook('Page(pw_template=inmain)::xrefDeleteUrl', function($event) {
			$event->return = self::xrefDeleteUrl($event->arguments(0), $event->arguments(1));
		});
	}
}
