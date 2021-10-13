<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemXrefUpcQuery, ItemXrefUpc;
// ProcessWire Classes, Modules
use ProcessWire\WireData, ProcessWire\Page, ProcessWire\XrefUpc as UpcCRUD;
// Dplus Configs
use Dplus\Configs;
// Dplus Filters
use Dplus\Filters\Min\Upcx as UpcxFilter;
// Mvc Controllers
use Controllers\Min\Upcx as UpcxController;

class Upcx extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->upc) === false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		self::sanitizeParameters($data, $fields);

		if ($data->action) {
			$upcx = UpcxController::getUpcx();
			$upcx->process_input(self::pw('input'));
		}
		$upc = $data->action == 'delete-upcx' ? '' : $data->upc;
		self::pw('session')->redirect(self::xrefUrl($data->itemID, $upc), $http301 = false);
	}

	private static function xref($data) {
		self::initHooks();
		$upcx = UpcxController::getUpcx();
		$xref = $upcx->getCreateXref($data->upc, $data->itemID);
		$page = self::pw('page');

		if ($xref->isNew()) {
			$page->headline = "ITM: $data->itemID UPCX: Create X-ref";
			$xref->setItemid($data->itemID);
		}
		if ($xref->isNew() == false) {
			$page->headline = "ITM: $data->itemID UPCX: $xref->upc";
		}

		$configs = new WireData();
		$configs->in = Configs\In::config();
		$page->js .= self::pw('config')->twig->render('items/upcx/form/js.twig', ['configs' => $configs]);
		$html = self::displayXref($data, $xref);
		self::pw('session')->removeFor('response', 'upcx');
		return $html;
	}

	private static function list($data) {
		self::initHooks();
		self::sanitizeParametersShort($data, ['itemID|text', 'q|text']);
		$upcx = UpcxController::getUpcx();
		$upcx->recordlocker->deleteLock();
		$page   = self::pw('page');
		$page->title    = "UPCs";
		$page->headline = "ITM: $data->itemID UPCX";
		$page->js       .= self::pw('config')->twig->render('items/upcx/list/.js.twig');

		$filter = new UpcxFilter();
		$filter->itemid($data->itemID);
		$filter->sortby($page);
		$upcs = $filter->query->paginate(self::pw('input')->pageNum, 10);

		$html = self::displayList($data, $upcs);
		self::pw('session')->removeFor('response', 'upcx');
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayXref($data, ItemXrefUpc $xref)  {
		$itm  = self::getItm();
		$item = $itm->get_item($data->itemID);

		$html = '';
		$html .= self::upcxHeaders();
		$html .= self::lockItem($data->itemID);
		$html .= UpcxController::lockXref($xref);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/upcx/form/display.twig', ['upcx' => UpcxController::getUpcx(), 'upc' => $xref, 'item' => $item, 'itm' => $itm]);
		return $html;
	}

	private static function upcxHeaders() {
		$html = '';
		$session = self::pw('session');
		$config  = self::pw('config');

		$html .= self::breadCrumbs();

		if ($session->getFor('response','upcx')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response','upcx')]);
		}
		return $html;
	}

	private static function displayList($data, $xrefs) {
		$itm  = self::getItm();
		$item = $itm->get_item($data->itemID);

		$html = '';
		$html .= self::upcxHeaders();
		$html .= self::lockItem($data->itemID);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/upcx/list/display.twig', ['upcs' => $xrefs, 'item' => $item, 'upcx' => UpcxController::getUpcx(), 'itm' => $itm]);
		return $html;
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

		$m->addHook('Page(pw_template=itm)::upcUrl', function($event) {
			$p = $event->object;
			$itemID  = self::pw('input')->get->text('itemID');
			$upc     = $event->arguments(0);
			$event->return = self::xrefUrl($itemID, $upc);
		});

		$m->addHook('Page(pw_template=itm)::upcDeleteUrl', function($event) {
			$p = $event->object;
			$itemID  = self::pw('input')->get->text('itemID');
			$upc     = $event->arguments(0);
			$event->return = self::xrefDeleteUrl($itemID, $upc);
		});

		$m->addHook('Page(pw_template=itm)::upcListUrl', function($event) {
			$p = $event->object;
			$itemID  = self::pw('input')->get->text('itemID');
			$event->return = Xrefs::xrefUrlUpcx($itemID);
		});

		$m->addHook('Page(pw_template=itm)::upcCreateUrl', function($event) {
			$itemID  = self::pw('input')->get->text('itemID');
			$event->return = self::xrefUrl($itemID, 'new');
		});
	}
}
