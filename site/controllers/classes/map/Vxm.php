<?php namespace Controllers\Map;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemXrefVendor;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefVxm as VxmCRUD;
// Dplus Filters
use Dplus\Filters\Map\Vendor as VendorFilter;
use Dplus\Filters\Map\Vxm    as VxmFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Vxm extends AbstractController {
	private static $vxm;

	public static function index($data) {
		$fields = ['vendorID|text', 'vendoritemID|text', 'q|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->vendorID) === false) {
			if (empty($data->vendoritemID) === false) {
				return self::xref($data);
			}
			return self::vendorXrefs($data);
		}
		return self::listVendors($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'vendorID|text', 'vendoritemID|text', 'itemID|text'];
		$data   = self::sanitizeParameters($data, $fields);
		$input  = self::pw('input');
		$vxm    = self::vxmMaster();

		if ($data->action) {
			$vxm->process_input($input);
		}
		$session = self::pw('session');
		$page    = self::pw('page');
		$url     = self::vendorUrl($data->vendorID);

		if ($vxm->xref_exists($data->vendorID, $data->vendoritemID, $data->itemID)) {
			$response = $vxm->response();

			if ($response && $response->has_success()) {
				$url = self::vendorFocusUrl($data->vendorID, $response->key);
			}
			$url = self::xrefUrl($data->vendorID, $data->vendoritemID, $data->itemID);
		}
		$session->redirect($url, $http301 = false);
	}

	public static function xref($data) {
		$fields = ['vendorID|text', 'vendoritemID|text', 'itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if ($data->action) {
			return self::handleCRUD($data);
		}

		$vxm    = self::vxmMaster();
		$vxm->init_field_attributes_config();
		$xref   = $vxm->get_create_xref($data->vendorID, $data->vendoritemID, $data->itemID);
		$page   = self::pw('page');

		if ($xref->isNew()) {
			$page->headline = "VXM: New X-ref";
		}
		if ($xref->isNew() === false) {
			$page->headline = "VXM: " . $vxm->get_recordlocker_key($xref);
		}

		$page->js .= self::pw('config')->twig->render('items/vxm/item/form/js.twig', ['page' => $page, 'vxm' => $vxm, 'item' => $xref]);
		$html = self::xrefDisplay($data, $xref);
		return $html;
	}

	private static function xrefDisplay($data, ItemXrefVendor $xref) {
		$config = self::pw('config');
		$qnotes = self::pw('modules')->get('QnotesItemVxm');
		$vxm    = self::vxmMaster();
		$vendor = $vxm->get_vendor($data->vendorID);

		$html = '';
		$html .= $config->twig->render('items/vxm/bread-crumbs.twig');
		$html .= self::lockXref($xref);
		$html .= $config->twig->render('items/vxm/item/form/display.twig', ['vendor' => $vendor, 'item' => $xref, 'vxm' => $vxm, 'qnotes' => $qnotes]);

		if (!$xref->isNew()) {
			$html .= self::qnotesDisplay($xref);
		}
		return $html;
	}

	public static function qnotesDisplay(ItemXrefVendor $xref) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$qnotes = self::pw('modules')->get('QnotesItemVxm');
		$html = "<hr>";
		if (self::pw('session')->response_qnote) {
			$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => self::pw('session')->response_qnote]);
		}
		$page->searchURL = self::pw('pages')->get('pw_template=msa-noce-ajax')->url;
		$html .= $config->twig->render('items/vxm/notes/notes.twig', ['qnotes' => $qnotes, 'item' => $xref]);
		$page->js .= $config->twig->render('items/vxm/notes/js.twig');
		self::pw('session')->remove('response_qnote');
		return $html;
	}

	public static function lockXref(ItemXrefVendor $xref) {
		$html = '';
		$vxm = self::vxmMaster();
		if ($xref->isNew() === false) {
			if ($vxm->lockrecord($xref) === false) {
				$msg = "VXM ". $vxm->get_recordlocker_key($xref) ." is being locked by " . $vxm->recordlocker->getLockingUser($vxm->get_recordlocker_key($xref));
				$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "VXM ".$vxm->get_recordlocker_key($xref)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$html .= '<div class="mb-3"></div>';
			}
		}
		return $html;
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['vendorID|text']);
		if ($data->vendorID) {
			return self::vendorXrefs($data);
		}
		return self::listVendors($data);
	}

	public static function listVendors($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$page   = self::pw('page');
		$vxm    = self::vxmMaster();
		$vxm->recordlocker->deleteLock();
		$filter = new VendorFilter();
		$filter->init();
		$filter->vendorid($vxm->vendorids());

		if ($data->q) {
			$page->headline = "Searching Vendors for '$data->q'";
			$filter->search($data->q);
		}
		$filter->sortby($page);
		$vendors = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$page->js .= self::pw('config')->twig->render('items/vxm/search/vendor/js.twig');
		$html = self::listVendorsDisplay($data, $vendors);
		return $html;
	}

	private static function listVendorsDisplay($data, PropelModelPager $vendors) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('items/vxm/search/vendor/page.twig', ['vendors' => $vendors]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $vendors]);
		$html .= $config->twig->render('items/vxm/new-xref-modal.twig');
		return $html;
	}

	public static function vendorXrefs($data) {
		$data = self::sanitizeParametersShort($data, ['vendorID|text']);
		$page   = self::pw('page');
		$vxm    = self::vxmMaster();
		$vxm->recordlocker->deleteLock();

		$filter = new VxmFilter();
		$filter->vendorid($data->vendorID);
		$filter->sortby($page);
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$page->headline = "VXM: Vendor $data->vendorID";
		$page->show_breadcrumbs = false;
		$page->js .= self::pw('config')->twig->render('items/vxm/list/item/js.twig');
		$html = self::vendorXrefsDisplay($data, $xrefs);
		return $html;
	}

	private static function vendorXrefsDisplay($data, PropelModelPager $xrefs) {
		$vxm    = self::vxmMaster();
		$vendor = $vxm->get_vendor($data->vendorID);

		$html = self::pw('config')->twig->render('items/vxm/list/item/vendor/display.twig', ['vxm' => $vxm, 'items' => $xrefs, 'vendor' => $vendor]);
		return $html;
	}

/* =============================================================
	Masters
============================================================= */
	public static function vxmMaster() {
		if (empty(self::$vxm)) {
			self::$vxm = self::pw('modules')->get('XrefVxm');
		}
		return self::$vxm;
	}

/* =============================================================
	URL Functions
============================================================= */
	/**
	 * Return URL for Vxm Vendor
	 * @param  string $vendorID  Vendor ID
	 * @return string
	 */
	public static function vendorUrl($vendorID) {
		$url = new Purl(self::pw('pages')->get('pw_template=vxm')->url);
		$url->query->set('vendorID', $vendorID);
		return $url->getUrl();
	}

	/**
	 * Return URL for Vxm Vendor with focus on an x-ref
	 * @param  string $vendorID  Vendor ID
	 * @param  string $focus     X-ref Key
	 * @return string
	 */
	public static function vendorFocusUrl($vendorID, $focus = '') {
		if (empty($focus)) {
			return self::vendorUrl($vendorID);
		}
		$url = new Purl(self::vendorUrl($vendorID));
		$vxm = self::vxmMaster();
		$xref = $vxm->xref_by_recordlocker_key($focus);

		if ($xref) {
			$page = self::pw('pages')->get('template=vxm');
			$url->query->set('focus', $focus);
			$filter = new VxmFilter();
			$filter->vendorid($vendorID);
			$position = $filter->position($xref);
			$pagenbr = ceil($position / self::pw('session')->display);
			$url = self::pw('modules')->get('Dpurl')->paginate($url, $page->name, $pagenbr);
		}
		return $url->getUrl();
	}

	/**
	 * Return URL for VXM and add focus if provided
	 * @param  string $vendorID  Vendor ID to highlight / focus
	 * @return string
	 */
	public static function vendorListUrl($vendorID = '') {
		$page = self::pw('pages')->get('pw_template=vxm');
		if (empty($vendorID)) {
			return $page->url;
		}
		$url = new Purl($page->url);
		$vxm = self::vxmMaster();
		$filter = new VendorFilter();
		$filter->init();

		if ($filter->exists($vendorID)) {
			$url->query->set('focus', $vendorID);
			$filter->vendorid($vxm->vendorids());
			$position = $filter->positionById($vendorID);
			$pagenbr = ceil($position / (self::pw('session')->display - 1));
			$url = self::pw('modules')->get('Dpurl')->paginate($url, $page->name, $pagenbr);
		}
		return $url->getUrl();
	}

	/**
	 * Return X-ref List Url for Item ID
	 * @param  string $itemID        Item ID
	 * @return string
	 */
	public static function xrefsByItemidUrl($vendorID, $vendoritemID, $itemID) {
		$url = new Purl(self::pw('pages')->get('pw_template=vxm')->url);
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}

	/**
	 * Return URL for Vxm X-ref
	 * @param  string $vendorID      Vendor ID
	 * @param  string $vendoritemID  Vendor Item ID
	 * @param  string $itemID        Item ID
	 * @return string
	 */
	public static function xrefUrl($vendorID, $vendoritemID, $itemID) {
		$url = new Purl(self::pw('pages')->get('pw_template=vxm')->url);
		$url->query->set('vendorID', $vendorID);
		$url->query->set('vendoritemID', $vendoritemID);
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}

	/**
	 * Return URL for Vxm X-ref Deletion
	 * @param  string $vendorID      Vendor ID
	 * @param  string $vendoritemID  Vendor Item ID
	 * @param  string $itemID        Item ID
	 * @return string
	 */
	public static function xrefDeleteUrl($vendorID, $vendoritemID, $itemID) {
		$url = new Purl(self::xrefUrl($vendorID, $vendoritemID, $itemID));
		$url->query->set('action', 'delete-xref');
		return $url->getUrl();
	}

/* =============================================================
	Hook Functions
============================================================= */
	public static function init() {
		$m = self::vxmMaster();

		$m->addHook("Page(pw_template=vxm)::vendorUrl", function($event) {
			$p = $event->object;
			$vendorID = $event->arguments(0); // To focus on
			$event->return = self::vendorUrl($vendorID);
		});

		$m->addHook("Page(pw_template=vxm)::vendorListUrl", function($event) {
			$p = $event->object;
			$vendorID = $event->arguments(0); // To focus on
			$event->return = self::vendorListUrl($vendorID);
		});

		$m->addHook('Page(pw_template=vxm)::xrefExitUrl', function($event) {
			$p = $event->object;
			$xref = $event->arguments(0); // Xref
			$vxm  = self::vxmMaster();
			$event->return = self::vendorFocusUrl($xref->vendorid, $vxm->get_recordlocker_key($xref));
		});

		$m->addHook('Page(pw_template=vxm)::xrefUrl', function($event) {
			$p = $event->object;
			$vendorID     = $event->arguments(0);
			$vendoritemID = $event->arguments(1);
			$itemID       = $event->arguments(2);
			$event->return = self::xrefUrl($vendorID, $vendoritemID, $itemID);
		});

		$m->addHook('Page(pw_template=vxm)::xrefsByItemidUrl', function($event) {
			$p = $event->object;
			$itemID       = $event->arguments(0);
			$event->return = self::xrefsByItemidUrl($itemID);
		});

		$m->addHook('Page(pw_template=vxm)::xrefDeleteUrl', function($event) {
			$p = $event->object;
			$vendorID     = $event->arguments(0);
			$vendoritemID = $event->arguments(1);
			$itemID       = $event->arguments(2);
			$event->return = self::xrefDeleteUrl($vendorID, $vendoritemID, $itemID);
		});
	}
}
