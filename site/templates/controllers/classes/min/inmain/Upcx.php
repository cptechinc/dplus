<?php namespace Controllers\Min\Inmain;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemXrefUpcQuery, ItemXrefUpc;
// ProcessWire Classes, Modules
use ProcessWire\WireData, ProcessWire\Page, ProcessWire\XrefUpc as UpcCRUD;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as Locker;
// Dplus Configs
use Dplus\Configs;
// Dplus Filters
use Dplus\Filters;
use Dplus\Filters\Min\Upcx as UpcxFilter;

class Upcx extends AbstractController {
	const DPLUSPERMISSION = 'upcx';
	private static $upcx;

/* =============================================================
	Index Functions
============================================================= */
	public static function index($data) {
		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}
		// Sanitize Params, parse route from params
		$fields = ['upc|string', 'itemID|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->upc) === false) {
			return self::upc($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateUserPermission() === false) {
			return self::pw('session')->redirect(self::url(), $http301 = false);
		}
		$fields = ['action|text', 'upc|string', 'itemID|string',];
		self::sanitizeParameters($data, $fields);

		if (empty($data->action) === false) {
			$upcx = self::getUpcx();
			$upcx->process_input(self::pw('input'));

			switch($data->action) {
				case 'delete-upcx':
					self::pw('session')->redirect(self::upcListUrl(), $http301 = false);
					break;
				case 'update-upcx':
					self::pw('session')->redirect(self::upcListUrl(implode(Locker::glue(), [$data->upc, $data->itemID])), $http301 = false);
					break;
				default:
					self::pw('session')->redirect(self::upcUrl($data->upc, $data->itemID), $http301 = false);
					break;
			}
		}
		self::pw('session')->redirect(self::upcUrl($data->upc, $data->itemID), $http301 = false);
	}

	private static function upc($data) {
		self::sanitizeParametersShort($data, ['upc|string', 'itemID|string', 'action|text']);
		self::pw('page')->show_breadcrumbs = false;

		$upcx = self::getUpcx();
		$xref = $upcx->getCreateXref($data->upc, $data->itemID);
		$page = self::pw('page');
		$page->headline = "UPCX: Create X-Ref";

		if ($xref->isNew() === false) {
			$upcx->lockrecord($xref);
			$page->headline = "UPCX: $xref->upc - $xref->itemid";
		}

		$configs = new WireData();
		$configs->in = Configs\In::config();
		$page->js   .= self::pw('config')->twig->render('items/upcx/form/js.twig', ['configs' => $configs]);
		self::initHooks();
		$html = self::displayUpc($data, $xref);
		self::pw('session')->removeFor('response', 'upcx');
		return $html;
	}

	private static function list($data) {
		self::sanitizeParametersShort($data, ['q|text', 'orderby|text']);
		self::pw('session')->removeFor('upcx', 'sortfilter');
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = "UPC Item X-Ref";

		$upcx = self::getUpcx();
		$upcx->recordlocker->deleteLock();
		$filter = new UpcxFilter();

		if ($data->q) {
			self::pw('page')->headline = "UPCX: Searching for '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby(self::pw('page'));

		if (empty($data->q) === false || empty($data->orderby) === false) {
			$sortFilter = Filters\SortFilter::fromArray(['q' => $data->q, 'orderby' => $data->orderby]);
			$sortFilter->saveToSession('upcx');
		}

		$filter->query->orderBy(ItemXrefUpc::aliasproperty('upc'), 'ASC');
		$upcs = $filter->query->paginate(self::pw('input')->pageNum, 10);

		self::pw('page')->js .= self::pw('config')->twig->render('items/upcx/list/.js.twig');
		self::initHooks();
		$html = self::displayList($data, $upcs);
		self::pw('session')->removeFor('response', 'upcx');
		return $html;
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayList($data, PropelModelPager $xrefs) {
		$upcx = self::getUpcx();
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('items/upcx/list/page.twig', ['upcx' => $upcx, 'upcs' => $xrefs]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $xrefs]);
		return $html;
	}

	private static function displayUpc($data, ItemXrefUpc $xref) {
		$html = '';
		$html .= self::pw('config')->twig->render('items/upcx/bread-crumbs.twig', ['upcx' => self::getUpcx(), 'upc' => $xref]);
		$html .= '<div class="mb-3">'.self::displayLocked($data, $xref).'</div>';
		$html .= self::pw('config')->twig->render('items/upcx/form/page.twig', ['upcx' => self::getUpcx(), 'upc' => $xref]);
		return $html;
	}

	public static function lockXref(ItemXrefUpc $xref) {
		$html = '';
		$upcx = self::getUpcx();
		$key  = $upcx->getRecordlockerKey($xref);

		if (!$xref->isNew()) {
			if (!$upcx->lockrecord($xref)) {
				$msg = "UPC ". $key ." is being locked by " . $upcx->recordlocker->getLockingUser($key);
				$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "UPC $key is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$html .= '<div class="mb-3"></div>';
			}
		}
		return $html;
	}

	private static function displayLocked($data, ItemXrefUpc $xref) {
		$upcx = self::getUpcx();
		$key  = $upcx->getRecordlockerKey($xref);

		if ($upcx->recordlocker->isLocked($key) && $upcx->recordlocker->userHasLocked($key) === false) {
			$msg = "UPC $xref->upc - $xref->itemid is being locked by " . $upcx->recordlocker->getLockingUser($key);
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "UPC is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		}
		return '';
	}

	private static function displayResponse($data) {
		$upcx = self::getUpcx();
		$response = self::pw('session')->getFor('response', 'upcx');

		if (empty($response) || $response->success === false) {
			return '';
		}
		return self::pw('config')->twig->render('items/itm/response-alert.twig', ['response' => $response]);
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
	public static function upcUrl($upc, $itemID = '') {
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
	public static function upcListUrl($focus = '') {
		if ($focus == '') {
			return self::url();
		}
		return self::upcListFocusUrl($focus);
	}

	/**
	 * Return UPCX List Url
	 * @param  string $focus UPC to focus on
	 * @return string
	 */
	public static function upcListFocusUrl($focus = '') {
		$upcx = self::getUpcx();

		if ($focus == '' || $upcx->xrefExistsByKey($focus) === false) {
			return self::url();
		}

		$sortFilter = Filters\SortFilter::getFromSession('upcx');
		$xref   = $upcx->xrefByKey($focus);
		$filter = new UpcxFilter();

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
	public static function upcDeleteUrl($upc, $itemID) {
		$url = new Purl(self::url());
		$url->query->set('action', 'delete-upcx');
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
	public static function itemUpcsUrl($itemID) {
		$url = new Purl(self::url());
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}

	public static function getUpcx() {
		if (empty(self::$upcx)) {
			self::$upcx = self::pw('modules')->get('XrefUpc');
		}
		return self::$upcx;
	}

/* =============================================================
	Supplemental Functions
============================================================= */

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

		$m->addHook('Page(pw_template=inmain)::upcUrl', function($event) {
			$event->return = self::upcUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=inmain)::upcListUrl', function($event) {
			$event->return = self::upcListUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=inmain)::itemUpcsUrl', function($event) {
			$itemID = $event->arguments(0);
			$event->return = self::itemUpcsUrl($itemID);
		});

		$m->addHook('Page(pw_template=inmain)::upcCreateUrl', function($event) {
			$event->return = self::upcUrl('new');
		});

		$m->addHook('Page(pw_template=inmain)::upcDeleteUrl', function($event) {
			$event->return = self::upcDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=inmain)::upcCreateItemidUrl', function($event) {
			$event->return = self::upcUrl('new', $event->arguments(0));
		});
	}
}
