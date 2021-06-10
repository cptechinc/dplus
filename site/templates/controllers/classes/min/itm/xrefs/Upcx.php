<?php namespace Controllers\Min\Itm\Xrefs;
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
use Controllers\Min\Itm\Xrefs;
use Controllers\Min\Itm\Xrefs\XrefFunction;
use Controllers\Min\Upcx as UpcxController;

class Upcx extends XrefFunction {

	public static function index($data) {
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->upc) === false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$page    = self::pw('page');
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		$data = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if ($data->action) {
			$upcx = UpcxController::getUpcx();
			$upcx->process_input($input);
		}
		$upc = $data->action == 'delete-upcx' ? '' : $data->upc;
		self::pw('session')->redirect(self::xrefUrl($data->itemID, $upc), $http301 = false);
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

	public static function xref($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		$data = self::sanitizeParametersShort($data, ['itemID|text', 'upc|text', 'action|text']);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		self::initHooks();
		$upcx = UpcxController::getUpcx();
		$xref = $upcx->get_create_xref($data->upc);
		$page   = self::pw('page');

		if ($xref->isNew()) {
			$page->headline = "ITM: $data->itemID UPCX: Create X-ref";
			$xref->setItemid($data->itemID);
		}
		if ($xref->isNew() == false) {
			$page->headline = "ITM: $data->itemID UPCX: $xref->upc";
		}

		$page->js .= self::pw('config')->twig->render('items/upcx/form/js.twig', ['upc' => $xref]);
		$html = self::xrefDisplay($data, $xref);
		self::pw('session')->removeFor('response', 'upcx');
		return $html;
	}

	private static function xrefDisplay($data, ItemXrefUpc $xref)  {
		$itm    = self::getItm();
		$item = $itm->get_item($data->itemID);

		$html = '';
		$html .= self::upcxHeaders();
		$html .= UpcxController::lockXref($xref);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/upcx/form/display.twig', ['upcx' => UpcxController::getUpcx(), 'upc' => $xref, 'item' => $item]);
		return $html;
	}

	public static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		self::initHooks();
		$data = self::sanitizeParametersShort($data, ['itemID|text', 'q|text']);
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

		$html = self::listDisplay($data, $upcs);
		self::pw('session')->removeFor('response', 'upcx');
		return $html;
	}

	private static function listDisplay($data, $xrefs) {
		$itm     = self::getItm();
		$item = $itm->get_item($data->itemID);

		$html = '';
		$html .= self::upcxHeaders();
		$html .= self::pw('config')->twig->render('items/itm/xrefs/upcx/list/display.twig', ['upcs' => $xrefs, 'item' => $item, 'upcx' => UpcxController::getUpcx()]);
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
