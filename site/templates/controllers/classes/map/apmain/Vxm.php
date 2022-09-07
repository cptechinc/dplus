<?php namespace Controllers\Map\Apmain;
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

class Vxm extends AbstractController{
	const DPLUSPERMISSION = 'vxm';
	private static $vxm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['vendorID|text', 'vendoritemID|text', 'q|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;
		$page->title = 'Vendor Item X-Ref';

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		self::initHooks();

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
		self::sanitizeParameters($data, $fields);
		$input  = self::pw('input');
		$vxm    = self::vxmMaster();

		if ($data->action) {
			$vxm->process_input($input);
		}
		$session = self::pw('session');
		$url     = self::vendorUrl($data->vendorID);

		if ($vxm->xref_exists($data->vendorID, $data->vendoritemID, $data->itemID)) {
			$response = $vxm->response();

			$url = self::xrefUrl($data->vendorID, $data->vendoritemID, $data->itemID);

			if ($response && $response->has_success()) {
				$url = self::vendorFocusUrl($data->vendorID, $response->key);
			}
		}
		$session->redirect($url, $http301 = false);
	}

	private static function xref($data) {
		$fields = ['vendorID|text', 'vendoritemID|text', 'itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if ($data->action) {
			return self::handleCRUD($data);
		}

		$vxm    = self::vxmMaster();
		$vxm->init_field_attributes_config();
		$xref   = $vxm->get_create_xref($data->vendorID, $data->vendoritemID, $data->itemID);
		$page   = self::pw('page');

		if ($xref->isNew()) {
			$page->headline = "VXM: New X-Ref";
		}
		if ($xref->isNew() === false) {
			$page->headline = "VXM: " . $vxm->get_recordlocker_key($xref);
		}

		$page->js .= self::pw('config')->twig->render('items/vxm/xref/form/js.twig', ['page' => $page, 'vxm' => $vxm, 'item' => $xref]);
		$html = self::displayXref($data, $xref);
		$vxm->deleteResponse();
		return $html;
	}

	private static function listVendors($data) {
		self::sanitizeParametersShort($data, ['q|text']);
		$page   = self::pw('page');
		$vxm    = self::vxmMaster();
		$vxm->recordlocker->deleteLock();
		$filter = new VendorFilter();
		$filter->init();
		$filter->vendorid($vxm->vendorids());

		if ($data->q) {
			$page->headline = "VXM: Searching Vendors for '$data->q'";
			$filter->search($data->q);
		}
		$filter->sortby($page);
		$vendors = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$page->js .= self::pw('config')->twig->render('items/vxm/search/vendor/js.twig');
		$html = self::displayListVendors($data, $vendors);
		$vxm->deleteResponse();
		return $html;
	}

	private static function vendorXrefs($data) {
		$data = self::sanitizeParametersShort($data, ['vendorID|text']);
		$page   = self::pw('page');
		$vxm    = self::vxmMaster();
		$vxm->recordlocker->deleteLock();

		$filter = new VxmFilter();
		$filter->vendorid($data->vendorID);
		$filter->sortby($page);
		$page->headline = "VXM: Vendor $data->vendorID";
		if ($data->q) {
			$page->headline = "VXM: Searching $data->vendorID X-Refs for '$data->q'";
			$filter->search($data->q);
		}
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		$page->show_breadcrumbs = false;
		$page->js .= self::pw('config')->twig->render('items/vxm/list/xref/js.twig');
		$html = self::displayVendorXrefs($data, $xrefs);
		$vxm->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayXref($data, ItemXrefVendor $xref) {
		$config = self::pw('config');
		$qnotes = self::pw('modules')->get('QnotesItemVxm');
		$vxm    = self::vxmMaster();
		$vendor = $vxm->get_vendor($data->vendorID);

		$html = '';
		$html .= $config->twig->render('items/vxm/bread-crumbs.twig');
		$html .= self::lockXref($xref);
		$html .= $config->twig->render('items/vxm/xref/form/display.twig', ['vendor' => $vendor, 'item' => $xref, 'vxm' => $vxm, 'qnotes' => $qnotes]);

		if ($xref->isNew() === false && $vxm->recordlocker->userHasLocked($vxm->get_recordlocker_key($xref))) {
			$html .= self::qnotesDisplay($xref);
		}
		return $html;
	}

	public static function qnotesDisplay(ItemXrefVendor $xref) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$qnotes = self::pw('modules')->get('QnotesItemVxm');
		$html = "<hr>";
		$responseQnote = self::pw('session')->response_qnote;
		if (empty($responseQnote) === false && $responseQnote->has_success() === false) {
			$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $responseQnote]);
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

	private static function displayListVendors($data, PropelModelPager $vendors) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('items/vxm/search/vendor/page.twig', ['vendors' => $vendors]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $vendors]);
		$html .= $config->twig->render('items/vxm/new-xref-modal.twig');
		return $html;
	}

	private static function displayVendorXrefs($data, PropelModelPager $xrefs) {
		$vxm    = self::vxmMaster();
		$vendor = $vxm->get_vendor($data->vendorID);

		$html = self::pw('config')->twig->render('items/vxm/list/xref/vendor/display.twig', ['vxm' => $vxm, 'items' => $xrefs, 'vendor' => $vendor]);
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
		$url = new Purl(Menu::vxmUrl());
		$url->query->set('vendorID', $vendorID);
		return $url->getUrl();
	}

	/**
	 * Return URL for Vxm Vendor with focus on an x-ref
	 * @param  string $vendorID  Vendor ID
	 * @param  string $focus     X-Ref Key
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
			$pagenbr = self::getPagenbrFromOffset($position);
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
		if (empty($vendorID)) {
			return Menu::vxmUrl();
		}
		$url = new Purl(Menu::vxmUrl());
		$vxm = self::vxmMaster();
		$filter = new VendorFilter();
		$filter->init();

		if ($filter->exists($vendorID)) {
			$url->query->set('focus', $vendorID);
			$filter->vendorid($vxm->vendorids());
			$position = $filter->positionById($vendorID);
			$pagenbr = self::getPagenbrFromOffset($position);
			$url = self::pw('modules')->get('Dpurl')->paginate($url, 'vxm', $pagenbr);
		}
		return $url->getUrl();
	}

	/**
	 * Return X-Ref List Url for Item ID
	 * @param  string $itemID        Item ID
	 * @return string
	 */
	public static function xrefsByItemidUrl($vendorID, $vendoritemID, $itemID) {
		$url = new Purl(Menu::vxmUrl());
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}

	/**
	 * Return URL for Vxm X-Ref
	 * @param  string $vendorID      Vendor ID
	 * @param  string $vendoritemID  Vendor Item ID
	 * @param  string $itemID        Item ID
	 * @return string
	 */
	public static function xrefUrl($vendorID, $vendoritemID, $itemID) {
		$url = new Purl(Menu::vxmUrl());
		$url->query->set('vendorID', $vendorID);
		$url->query->set('vendoritemID', $vendoritemID);
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}

	/**
	 * Return URL for Vxm X-Ref Deletion
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
	public static function initHooks() {
		$m = self::vxmMaster();

		$m->addHook('Page(pw_template=apmain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook("Page(pw_template=apmain)::vendorUrl", function($event) {
			$p = $event->object;
			$vendorID = $event->arguments(0); // To focus on
			$event->return = self::vendorUrl($vendorID);
		});

		$m->addHook("Page(pw_template=apmain)::vendorListUrl", function($event) {
			$p = $event->object;
			$vendorID = $event->arguments(0); // To focus on
			$event->return = self::vendorListUrl($vendorID);
		});

		$m->addHook('Page(pw_template=apmain)::xrefExitUrl', function($event) {
			$p = $event->object;
			$xref = $event->arguments(0); // Xref
			$vxm  = self::vxmMaster();
			$event->return = self::vendorFocusUrl($xref->vendorid, $vxm->get_recordlocker_key($xref));
		});

		$m->addHook('Page(pw_template=apmain)::xrefUrl', function($event) {
			$p = $event->object;
			$vendorID     = $event->arguments(0);
			$vendoritemID = $event->arguments(1);
			$itemID       = $event->arguments(2);
			$event->return = self::xrefUrl($vendorID, $vendoritemID, $itemID);
		});

		$m->addHook('Page(pw_template=apmain)::xrefsByItemidUrl', function($event) {
			$p = $event->object;
			$itemID       = $event->arguments(0);
			$event->return = self::xrefsByItemidUrl($itemID);
		});

		$m->addHook('Page(pw_template=apmain)::xrefDeleteUrl', function($event) {
			$p = $event->object;
			$vendorID     = $event->arguments(0);
			$vendoritemID = $event->arguments(1);
			$itemID       = $event->arguments(2);
			$event->return = self::xrefDeleteUrl($vendorID, $vendoritemID, $itemID);
		});
	}
}
