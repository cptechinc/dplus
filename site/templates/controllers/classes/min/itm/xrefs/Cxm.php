<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemXrefCustomerQuery, ItemXrefCustomer;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefCxm as CxmCRUD;
// Dplus Filters
use Dplus\Filters\Mso\Cxm as CxmFilter;
// Mvc Controllers
use Controllers\Mso\Somain\Cxm as CxmController;

class Cxm extends Base {
	const PERMISSION_ITMP = 'xrefs';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->custitemID) == false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$page    = self::pw('page');
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}
		$fields = ['itemID|text', 'custID|text', 'custitemID|text', 'action|text'];
		self::sanitizeParameters($data, $fields);

		$cxm = CxmController::getCxm();

		if ($data->action) {
			if (strpos($data->action, 'notes') !== false) {
				CxmController::getQnotes()->processInput(self::pw('input'));
			} else {
				$cxm->processInput(self::pw('input'));
			}
		}

		$response = $cxm->getResponse();
		$url = self::xrefListUrl($data->itemID);

		if ($cxm->exists($data->custID, $data->custitemID)) {
			$url = self::xrefUrl($data->custID, $data->custitemID, $data->itemID);

			if ($response && $response->hasSuccess()) {
				$url = self::xrefListUrl($data->itemID, $response->key);
			}
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function xref($data) {
		self::initHooks();
		$page  = self::pw('page');
		$cxm   = CxmController::getCxm();
		$xref = $cxm->getOrCreateXref($data->custID, $data->custitemID);
		$page->headline = "ITM: $xref->itemid CXM $xref->custid-$xref->custitemid";

		if ($xref->isNew()) {
			$xref->setItemid($data->itemID);
			$page->headline = "ITM: $xref->itemid CXM Create X-ref";
		}
		if ($xref->isNew() === false) {
			$cxm->lockrecord($xref);
		}
		if ($xref->isNew() === false && $cxm->recordlocker->userHasLocked($cxm->getRecordlockerKey($xref))) {
			$qnotes = CxmController::getQnotes();
			$page->js .= self::pw('config')->twig->render('items/cxm/.new/xref/qnotes/.js.twig', ['qnotes' => $qnotes]);
			$page->js .= self::pw('config')->twig->render('msa/noce/ajax/js.twig');
		}
		$page->js .= self::pw('config')->twig->render('items/cxm/.new/xref/.js.twig', ['cxm' => $cxm, 'xref' => $xref]);
		$html = self::xrefDisplay($data, $xref);
		$cxm->deleteResponse();
		return $html;
	}

	private static function list($data) {
		$fields = ['itemID|text', 'q|text'];
		self::sanitizeParametersShort($data, $fields);
		self::initHooks();
		$input   = self::pw('input');
		$page    = self::pw('page');

		$cxm  = CxmController::getCxm();
		$cxm->recordlocker->deleteLock();
		$filter = new CxmFilter();
		$filter->filterInput($input);
		$filter->sortby($page);
		$xrefs = $filter->query->paginate($input->pageNum, 10);
		$page->title = "CXM";
		$page->headline = "ITM: $data->itemID CXM";

		$page->js .= self::pw('config')->twig->render('items/cxm/.new/customer/xrefs/.js.twig');
		$html = self::listDisplay($data, $xrefs);
		$cxm->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function xrefDisplay($data, ItemXrefCustomer $xref) {

		$cxm  = CxmController::getCxm();
		$qnotes = CxmController::getQnotes();

		$html = '';
		$html .= self::renderCxmHeaders();
		$html .= CxmController::renderXrefIsLockedAlert($xref);
		$html .= self::renderXref($data, $xref);
		return $html;
	}

	private static function renderCxmHeaders() {
		$html = self::breadCrumbs();
		$html .= CxmController::renderCxmResponses();
		return $html;
	}

	private static function renderXref($data, ItemXrefCustomer $xref) {
		$itm     = self::getItm();
		$item = $itm->get_item($data->itemID);
		$cxm    = CxmController::getCxm();
		$qnotes = CxmController::getQnotes();
		$customer = $cxm->customer($data->custID);


		return self::pw('config')->twig->render('items/itm/xrefs/cxm/form/display.twig', ['xref' => $xref, 'item' => $item, 'customer' => $customer, 'itm' => $itm, 'cxm' => $cxm,  'qnotes' => $qnotes]);
	}

	private static function listDisplay($data, PropelModelPager $xrefs) {
		$itm  = self::getItm();
		$item = $itm->get_item($data->itemID);

		$html = '';
		$html .= self::renderCxmHeaders();
		$html .= self::lockItem($data->itemID);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/cxm/list/display.twig', ['item' => $item, 'itm' => $itm, 'cxm' => CxmController::getCxm(), 'items' => $xrefs]);
		return $html;
	}

/* =============================================================
	URL Functions
============================================================= */
	/**
	 * Return URL for X-ref List
	 * @param  string $itemID      Item ID
	 * @param  string $focus
	 * @return string
	 */
	public static function xrefListUrl($itemID, $focus = '') {
		$url = new Purl(Xrefs::xrefUrlCxm($itemID));
		if ($focus) {
			$url->query->set('focus', $focus);
		}
		return $url->getUrl();
	}

	/**
	 * Return URL for Vxm X-ref
	 * @param  string $custID      Customer ID
	 * @param  string $custitemID  Customer Item ID
	 * @return string
	 */
	public static function xrefUrl($custID, $custitemID, $itemID) {
		$url = new Purl(Xrefs::xrefUrlCxm($itemID));
		$url->query->set('custID', $custID);
		$url->query->set('custitemID', $custitemID);
		return $url->getUrl();
	}

	/**
	 * Return URL for Vxm X-ref Deletion
	 * @param  string $custID      Customer ID
	 * @param  string $custitemID  Customer Item ID
	 * @return string
	 */
	public static function xrefDeleteUrl($custID, $custitemID, $itemID = '') {
		$url = new Purl(self::xrefUrl($custID, $custitemID, $itemID));
		$url->query->set('action', 'delete-xref');
		return $url->getUrl();
	}

/* =============================================================
	Hook Functions
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=itm)::xrefUrl', function($event) {
			$p = $event->object;
			$custID     = $event->arguments(0);
			$custitemID = $event->arguments(1);
			$itemID     = $event->arguments(2);
			$event->return = self::xrefUrl($custID, $custitemID, $itemID);
		});

		$m->addHook('Page(pw_template=itm)::xrefNewUrl', function($event) {
			$p = $event->object;
			$itemID = $event->arguments(0);
			$event->return = self::xrefUrl($custID = '', $custitemID = 'new', $itemID);
		});

		$m->addHook('Page(pw_template=itm)::xrefDeleteUrl', function($event) {
			$p = $event->object;
			$custID     = $event->arguments(0);
			$custitemID = $event->arguments(1);
			$itemID      = $event->arguments(2);
			$event->return = self::xrefDeleteUrl($custID, $custitemID, $itemID);
		});

		$m->addHook('Page(pw_template=itm)::xrefExitUrl', function($event) {
			$p = $event->object;
			$xref = $event->arguments(0); // Xref
			$event->return = Xrefs::xrefUrlCxm($xref->itemid);
		});
	}
}
