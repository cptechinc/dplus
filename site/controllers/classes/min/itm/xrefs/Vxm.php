<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemXrefVendorQuery, ItemXrefVendor;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefVxm as VxmCRUD;
// DplusFilters
use Dplus\Filters\Map\Vxm as VxmFilter;
// Mvc Controllers
use Controllers\Min\Itm\Xrefs;
use Controllers\Min\Itm\Xrefs\XrefFunction;
use Controllers\Map\Vxm as VxmController;

class Vxm extends XrefFunction {
	const PERMISSION_ITMP = 'xrefs';

	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}

		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->vendoritemID) == false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$page    = self::pw('page');
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}
		$fields = ['itemID|text', 'vendorID|text', 'vendoritemID|text', 'action|text'];
		$data  = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');
		$vxm   = VxmController::vxmMaster();

		if ($data->action) {
			$vxm->process_input($input);
		}
		$session = self::pw('session');
		$page    = self::pw('page');
		$response = $session->getFor('response', 'vxm');
		$url = Xrefs::xrefUrlVxm($itemID);

		if ($vxm->xref_exists($data->vendorID, $data->vendoritemID, $data->itemID)) {
			$url = self::xrefUrl($data->vendorID, $data->vendoritemID, $data->itemID);

			if ($response && $response->has_success()) {
				$xref = $vxm->xref($data->vendorID, $data->vendoritemID, $data->itemID);
				$url  = Xrefs::xrefUrlVxm($itemID);
			}
		}
		$session->redirect($url, $http301 = false);
	}

	public static function xref($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}

		$fields = ['itemID|text', 'vendorID|text', 'vendoritemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if ($data->action) {
			return self::handleCRUD($data);
		}
		self::initHooks();

		$vxm     = VxmController::vxmMaster();
		$xref = $vxm->get_create_xref($data->vendorID, $data->vendoritemID, $data->itemID);

		$page    = self::pw('page');
		$page->headline = "ITM: $data->itemID VXM $xref->vendorid-$xref->vendoritemid";
		$page->js .= self::pw('config')->twig->render('items/vxm/item/form/js.twig', ['vxm' => $vxm]);

		if ($xref->isNew()) {
			$xref->setItemid($data->itemID);
			$page->headline = "ITM: $data->itemID VXM Create X-ref";
		}
		$html = self::xrefDisplay($data, $xref);
		self::pw('session')->removeFor('response', 'vxm');
		return $html;
	}

	private static function xrefDisplay($data, ItemXrefVendor $xref) {
		$qnotes = self::pw('modules')->get('QnotesItemVxm');
		$itm    = self::getItm();
		$item   = $itm->get_item($data->itemID);

		$html = '';
		$html .= self::vxmHeaders();
		$html .= VxmController::lockXref($xref);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/vxm/form/display.twig', ['xref' => $xref, 'item' => $item, 'vxm' => VxmController::vxmMaster(), 'qnotes' => $qnotes]);

		if (!$xref->isNew()) {
			$html .= VxmController::qnotesDisplay($xref);
		}
		return $html;
	}

	private static function vxmHeaders() {
		$html = '';
		$session = self::pw('session');
		$config  = self::pw('config');

		$html .= self::breadCrumbs();

		if ($session->getFor('response','vxm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response','vxm')]);
		}
		return $html;
	}

	public static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}
		self::initHooks();
		$data    = self::sanitizeParametersShort($data, ['itemID|text', 'q|text']);
		VxmController::vxmMaster()->recordlocker->deleteLock();

		$page    = self::pw('page');
		$filter = new VxmFilter();
		$filter->itemid($data->itemID);
		$filter->sortby($page);
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 10);
		$page->title = "VXM";
		$page->headline = "ITM: $data->itemID VXM";
		$page->js .= self::pw('config')->twig->render('items/vxm/list/item/js.twig');
		$html = self::listDisplay($data, $xrefs);
		self::pw('session')->removeFor('response', 'vxm');
		return $html;
	}

	private static function listDisplay($data, PropelModelPager $xrefs) {
		$itm     = self::getItm();
		$item = $itm->get_item($data->itemID);

		$html = '';
		$html .= self::vxmHeaders();
		$html .= self::pw('config')->twig->render('items/itm/xrefs/vxm/list/display.twig', ['item' => $item, 'items' => $xrefs, 'vxm' => VxmController::vxmMaster()]);
		return $html;
	}

/* =============================================================
	Url Functions
============================================================= */
	/**
	 * Return Url to X-ref
	 * @param  string $vendorID     Vendor ID
	 * @param  string $vendoritemID Vendor Item ID
	 * @param  string $itemID       Item ID
	 * @return string
	 */
	public static function xrefUrl($vendorID, $vendoritemID, $itemID) {
		$url = new Purl(Xrefs::xrefUrlVxm($itemID));
		$url->query->set('vendorID', $vendorID);
		$url->query->set('vendoritemID', $vendoritemID);
		return $url->getUrl();
	}

	/**
	 * Return Url to Delete X-ref
	 * @param  string $vendorID     Vendor ID
	 * @param  string $vendoritemID Vendor Item ID
	 * @param  string $itemID       Item ID
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
		$m = VxmController::vxmMaster();

		$m->addHook('Page(pw_template=itm)::xrefUrl', function($event) {
			$p = $event->object;
			$vendorID     = $event->arguments(0);
			$vendoritemID = $event->arguments(1);
			$itemID       = $event->arguments(2);
			$event->return = self::xrefUrl($vendorID, $vendoritemID, $itemID);
		});

		$m->addHook('Page(pw_template=itm)::xrefDeleteUrl', function($event) {
			$p = $event->object;
			$vendorID     = $event->arguments(0);
			$vendoritemID = $event->arguments(1);
			$itemID       = $event->arguments(2);
			$event->return = self::xrefDeleteUrl($vendorID, $vendoritemID, $itemID);
		});

		$m->addHook('Page(pw_template=itm)::xrefExitUrl', function($event) {
			$p = $event->object;
			$xref = $event->arguments(0); // Xref
			$event->return = Xrefs::xrefUrlVxm($xref->itemid);
		});
	}
}
