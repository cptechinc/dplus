<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use itemMasterItem, ItemXrefUpc;
// ProcessWire Classes, Modules
use ProcessWire\WireData, ProcessWire\Page;
// Dplus Configs
use Dplus\Configs;
// Dplus Filters
use Dplus\Filters\Min\Upcx as UpcxFilter;
// Mvc Controllers
use Controllers\Min\Inmain\Upcx as UpcxController;

class Upcx extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		$itm  = self::getItm();
		$item = $itm->get_item($data->itemID);

		if (empty($data->upc) === false) {
			return self::xref($data, $item);
		}
		return self::list($data, $item);
	}

	public static function handleCRUD(WireData $data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		self::sanitizeParameters($data, $fields);

		if ($data->action) {
			$upcx = UpcxController::getUpcx();
			$upcx->processInput(self::pw('input'));
		}
		$upc = $data->action == 'delete' ? '' : $data->upc;
		self::pw('session')->redirect(self::xrefUrl($data->itemID, $upc), $http301 = false);
	}

	private static function xref(WireData $data, ItemMasterItem $item) {
		$upcx = UpcxController::getUpcx();
		$xref = $upcx->getOrCreateXref($data->upc, $data->itemID);
		$page = self::pw('page');
		$page->headline = "ITM: $data->itemID UPCX: New X-Ref";
		
		if ($xref->isNew() === false) {
			$upcx->lockrecord($xref);
			$page->headline = "ITM: $data->itemID UPCX: $xref->upc";
		}

		self::initHooks();
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/events/ajax-modal.js'));
		$page->js .= UpcxController::renderXrefJs($data);
		$html = self::displayXref($data, $item, $xref);
		$upcx->deleteResponse();
		return $html;
	}

	private static function list(WireData $data, ItemMasterItem $item) {
		self::initHooks();
		self::sanitizeParametersShort($data, ['itemID|text', 'q|text']);
		$upcx = UpcxController::getUpcx();
		$upcx->recordlocker->deleteLock();
		$page   = self::pw('page');
		$page->headline = "ITM: $data->itemID UPCX";
		$page->js       .= UpcxController::renderListJs($data);

		$filter = new UpcxFilter();
		$filter->itemid($data->itemID);
		$filter->sort(self::pw('input')->get);
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 10);

		$html = self::displayList($data, $item, $xrefs);
		$upcx->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayXref(WireData $data, ItemMasterItem $item, ItemXrefUpc $xref)  {
		$html = '';
		$html .= self::renderBreadcrumbs();
		$html .= self::lockItem($data->itemID);
		$html .= UpcxController::renderXrefIsLockedAlert($xref);
		$html .= UpcxController::renderResponse();
		$html .= self::renderXref($data, $item, $xref);
		return $html;
	}

	private static function displayList(WireData $data, ItemMasterItem $item,  PropelModelPager $xrefs) {
		$html = '';
		$html .= self::renderBreadcrumbs();
		$html .= self::lockItem($data->itemID);
		$html .= UpcxController::renderResponse();
		$html .= self::pw('config')->twig->render('items/itm/xrefs/upcx/list/display.twig', ['upcs' => $xrefs, 'item' => $item, 'upcx' => UpcxController::getUpcx(), 'itm' => self::getItm()]);
		return $html;
	}

/* =============================================================
	Render HTML
============================================================= */
	protected static function renderBreadcrumbs() {
		return self::breadCrumbs();
	}

	private static function renderList(WireData $data, ItemMasterItem $item, PropelModelPager $xrefs) {
		$upcx = UpcxController::getUpcx();
		$itm  = self::getItm();
		return self::pw('config')->twig->render('items/itm/xrefs/upcx/list/display.twig', ['upcs' => $xrefs, 'item' => $item, 'upcx' => $upcx, 'itm' => $itm]);
	}

	private static function renderXref(WireData $data, ItemMasterItem $item, ItemXrefUpc $xref) {
		$itm  = self::getItm();
		return self::pw('config')->twig->render('items/itm/xrefs/upcx/xref/display.twig', ['upcx' => UpcxController::getUpcx(), 'xref' => $xref, 'item' => $item, 'itm' => $itm]);
	}

/* =============================================================
	Url Functions
============================================================= */
	/**
	 * Return Url to X-ref
	 * @param  string $itemID  Item ID
	 * @param  string $upc     UPC
	 * @return string
	 */
	public static function xrefUrl($itemID, $upc = '') {
		$url = new Purl(Xrefs::xrefUrlUpcx($itemID));
		if ($upc) {
			$url->query->set('upc', $upc);
		}
		return $url->getUrl();
	}

	/**
	 * Return Url to Delete X-ref
	 * @param  string $itemID  Item ID
	 * @param  string $upc     UPC
	 * @return string
	 */
	public static function xrefDeleteUrl($itemID, $upc) {
		$url = new Purl(self::xrefUrl($itemID, $upc));
		$url->query->set('action', 'delete-upcx');
		return $url->getUrl();
	}

/* =============================================================
	Hook Functions
============================================================= */
	public static function initHooks() {
		$m = UpcxController::getUpcx();

		$m->addHook('Page(pw_template=itm)::xrefUrl', function($event) {
			$p = $event->object;
			$itemID  = self::pw('input')->get->text('itemID');
			$upc     = $event->arguments(0);
			$event->return = self::xrefUrl($itemID, $upc);
		});

		$m->addHook('Page(pw_template=itm)::xrefDeleteUrl', function($event) {
			$p = $event->object;
			$itemID  = self::pw('input')->get->text('itemID');
			$upc     = $event->arguments(0);
			$event->return = self::xrefDeleteUrl($itemID, $upc);
		});

		$m->addHook('Page(pw_template=itm)::xrefListUrl', function($event) {
			$p = $event->object;
			$itemID  = self::pw('input')->get->text('itemID');
			$event->return = Xrefs::xrefUrlUpcx($itemID);
		});

		$m->addHook('Page(pw_template=itm)::xrefCreateUrl', function($event) {
			$itemID  = self::pw('input')->get->text('itemID');
			$event->return = self::xrefUrl($itemID, 'new');
		});
	}
}
