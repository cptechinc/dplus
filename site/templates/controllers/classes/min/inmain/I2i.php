<?php namespace Controllers\Min\Inmain;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use Item2ItemQuery, Item2Item;
// ProcessWire Classes, Modules
use ProcessWire\WireData, ProcessWire\Page;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as Locker;
// Dplus Configs
use Dplus\Configs;
// Dplus Filters
use Dplus\Filters;
use Dplus\Filters\Min\I2i as I2iFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class I2i extends AbstractController {
	private static $upcx;

	public static function index($data) {
		$fields = ['parentID|text', 'childID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->parentID) === false && empty($data->childID) === false) {
			return self::upc($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'upc|text', 'itemID|text',];
		$data  = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');
	}

	public static function list($data) {
		self::sanitizeParametersShort($data, ['q|text', 'orderby|text']);
		self::pw('session')->removeFor('upcx', 'sortfilter');

		// $upcx = self::getUpcx();
		// $upcx->recordlocker->deleteLock();
		$filter = new I2iFilter();

		if ($data->q) {
			self::pw('page')->headline = "I2I: Searching for '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby(self::pw('page'));

		if (empty($data->q) === false || empty($data->orderby) === false) {
			$sortFilter = Filters\SortFilter::fromArray(['q' => $data->q, 'orderby' => $data->orderby]);
			$sortFilter->saveToSession('i2i');
		}

		//$filter->query->orderBy(ItemXrefUpc::aliasproperty('upc'), 'ASC');
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 10);

		// self::pw('page')->js .= self::pw('config')->twig->render('items/upcx/list/.js.twig');
		$html = self::listDisplay($data, $xrefs);
		return $html;
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function listDisplay($data, PropelModelPager $xrefs) {
		//$upcx = self::getUpcx();
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('min/i2i/list/display.twig', ['xrefs' => $xrefs]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $xrefs]);
		return $html;
	}

/* =============================================================
	URL Functions
============================================================= */
	/**
	 * Return URL to view / edit UPC
	 * @param  string $upc    UPC Code
	 * @param  string $itemID Item ID
	 * @return string
	 */
	public static function upcUrl($upc, $itemID = '') {
		$url = new Purl(self::pw('pages')->get("pw_template=upcx")->url);
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
			return self::pw('pages')->get("pw_template=upcx")->url;
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
		$page = self::pw('pages')->get("pw_template=upcx");

		if ($focus == '' || $upcx->xrefExistsByKey($focus) === false) {
			return $page->url;
		}

		$sortFilter = Filters\SortFilter::getFromSession('upcx');
		$xref   = $upcx->xrefByKey($focus);
		$filter = new UpcxFilter();

		if ($sortFilter) {
			$filter->applySortFilter($sortFilter);
		}

		$filter->query->orderBy(Item2Item::aliasproperty('upc'), 'ASC');
		$offset = $filter->position($xref);
		$pagenbr = self::getPagenbrFromOffset($offset);

		$url = new Purl($page->url);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, $page->name, $pagenbr);
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
		$url = new Purl(self::pw('pages')->get("pw_template=upcx")->url);
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
		$url = new Purl(self::pw('pages')->get("pw_template=upcx")->url);
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}

	public static function getUpcx() {
		if (empty(self::$upcx)) {
			self::$upcx = self::pw('modules')->get('XrefUpc');
		}
		return self::$upcx;
	}

	public static function initHooks() {
		$m = self::pw('modules')->get('XrefUpc');

		$m->addHook('Page(pw_template=upcx)::upcUrl', function($event) {
			$event->return = self::upcUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=upcx)::upcListUrl', function($event) {
			$event->return = self::upcListUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=upcx)::itemUpcsUrl', function($event) {
			$itemID = $event->arguments(0);
			$event->return = self::itemUpcsUrl($itemID);
		});

		$m->addHook('Page(pw_template=upcx)::upcCreateUrl', function($event) {
			$event->return = self::upcUrl('new');
		});

		$m->addHook('Page(pw_template=upcx)::upcDeleteUrl', function($event) {
			$event->return = self::upcDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=upcx)::upcCreateItemidUrl', function($event) {
			$event->return = self::upcUrl('new', $event->arguments(0));
		});
	}
}
