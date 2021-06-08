<?php namespace Controllers\Min;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemXrefUpcQuery, ItemXrefUpc;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefUpc as UpcCRUD;
// Dplus Filters
use Dplus\Filters\Min\Upcx as UpcxFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Upcx extends AbstractController {
	private static $upcx;

	public static function index($data) {
		$fields = ['upc|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->upc) === false) {
			return self::upc($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'upc|text'];
		$data  = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if (empty($data->action) === false) {
			$upcx = self::getUpcx();
			$upcx->process_input($input);

			switch($data->action) {
				case 'delete-upcx':
					self::pw('session')->redirect(self::upcListUrl(), $http301 = false);
					break;
				default:
					self::pw('session')->redirect(self::upcUrl($data->upc), $http301 = false);
					break;
			}
		}
		self::pw('session')->redirect(self::upcUrl($data->upc), $http301 = false);
	}

	public static function upc($data) {
		$data = self::sanitizeParametersShort($data, ['upc|text', 'action|text']);

		if ($data->action) {
			return self::handleCRUD($data);
		}

		$upcx = self::getUpcx();
		$xref = $upcx->get_create_xref($data->upc);
		$page = self::pw('page');
		$page->headline = "UPCX: $xref->upc";

		if ($xref->isNew()) {
			$page->headline = "UPCX: Create X-ref";
		}

		$page->js   .= self::pw('config')->twig->render('items/upcx/form/js.twig', ['upc' => $xref]);
		$html = self::upcDisplay($data, $xref);
		return $html;
	}

	private static function upcDisplay($data, ItemXrefUpc $xref) {
		$config = self::pw('config');

		$html = '';
		$html .= self::lockXref($xref);
		$html .= self::pw('config')->twig->render('items/upcx/form/page.twig', ['upcx' => self::getUpcx(), 'upc' => $xref]);
		return $html;
	}

	public static function lockXref(ItemXrefUpc $xref) {
		$config = self::pw('config');
		$upcx = self::getUpcx();
		$html = '';

		if ($upcx->recordlocker->isLocked($xref->upc) && !$upcx->recordlocker->userHasLocked($xref->upc)) {
			$msg = "UPC $code is being locked by " . $upcx->recordlocker->getLockingUser($xref->upc);
			$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "UPC $xref->upc is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		} elseif ($upcx->recordlocker->isLocked($xref->upc) === false) {
			$upcx->recordlocker->lock($xref->upc);
		}

		if ($xref->isNew()) {
			if ($xref->upc != '') {
				$html .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "UPC not found, you may create it below"]);
			}
		}
		$html .= '<div class="mb-3"></div>';
		return $html;
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$page = self::pw('page');
		$upcx = self::getUpcx();
		$upcx->recordlocker->deleteLock();
		$filter = new UpcxFilter();

		if ($data->q) {
			$page->headline = "UPCX: Results for '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby($page);
		$upcs = $filter->query->paginate(self::pw('input')->pageNum, 10);

		$page->js   .= self::pw('config')->twig->render('items/upcx/list/.js.twig');
		$html = self::listDisplay($data, $upcs);
		return $html;
	}

	private static function listDisplay($data, PropelModelPager $xrefs) {
		$upcx = self::getUpcx();
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('items/upcx/list/page.twig', ['upcx' => $upcx, 'upcs' => $xrefs]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $xrefs]);
		return $html;
	}

/* =============================================================
	URL Functions
============================================================= */
	/**
	 * Return URL to view / edit UPC
	 * @param  string $upc    UPC Code
	 * @param  string $itemID ** Optional
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

		if ($focus == '' || $upcx->xref_exists($focus) === false) {
			return $page->url;
		}

		$xref   = $upcx->xref($focus);
		$filter = new UpcxFilter();
		$offset = $filter->position($xref);
		$pagenbr = ceil($offset / self::pw('session')->display);
		$url = new Purl($page->url);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, $page->name, $pagenbr);
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}

	/**
	 * Return URL to delete UPC
	 * @return string
	 */
	public static function upcDeleteUrl($upc) {
		$url = new Purl(self::pw('pages')->get("pw_template=upcx")->url);
		$url->query->set('action', 'delete-upcx');
		$url->query->set('upc', $upc);
		return $url->getUrl();
	}

	/**
	 * Return URL to List the UPCs associated with the ItemID
	 * @param  string $itemID Item ID
	 * @return string
	 */
	public static function itemUpcsUrl($itemID) {
		$url = new Url(self::pw('pages')->get("pw_template=upcx")->url);
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}

	public static function getUpcx() {
		if (empty(self::$upcx)) {
			self::$upcx = self::pw('modules')->get('XrefUpc');
		}
		return self::$upcx;
	}

	public static function init() {
		$m = self::pw('modules')->get('XrefUpc');

		$m->addHook('Page(pw_template=upcx)::upcUrl', function($event) {
			$upc = $event->arguments(0);
			$event->return = self::upcUrl($upc);
		});

		$m->addHook('Page(pw_template=upcx)::upcListUrl', function($event) {
			$focus = $event->arguments(0);
			$event->return = self::upcListUrl($focus);
		});

		$m->addHook('Page(pw_template=upcx)::itemUpcsUrl', function($event) {
			$itemID = $event->arguments(0);
			$event->return = self::itemUpcsUrl($itemID);
		});

		$m->addHook('Page(pw_template=upcx)::upcCreateUrl', function($event) {
			$event->return = self::upcUrl('new');
		});

		$m->addHook('Page(pw_template=upcx)::upcDeleteUrl', function($event) {
			$upc = $event->arguments(0);
			$event->return = self::upcDeleteUrl($upc);
		});

		$m->addHook('Page(pw_template=upcx)::upcCreateItemidUrl', function($event) {
			$itemID = $event->arguments(0);
			$event->return = self::upcUrl('new', $itemID);
		});
	}
}
