<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
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
use Controllers\Min\Itm\Xrefs;
use Controllers\Min\Itm\Xrefs\XrefFunction;
use Controllers\Mso\Cxm as CxmController;

class Cxm extends XrefFunction {
	const PERMISSION_ITMP = 'xrefs';

	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		$page->show_breadcrumbs = false;

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
			return $page->body;
		}
		$fields = ['itemID|text', 'custID|text', 'custitemID|text', 'action|text'];
		$data  = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if ($data->action) {
			$cxm = CxmController::getCxm();
			$cxm->process_input($input);
		}
		$session  = self::pw('session');

		$response = $session->getFor('response', 'cxm');
		$url = self::xrefListUrl($data->itemID);


		if ($cxm->xref_exists($data->custID, $data->custitemID)) {
			$url = self::xrefUrl($data->custID, $data->custitemID, $data->itemID);

			if ($response && $response->has_success()) {
				$url = self::xrefListUrl($data->itemID, $response->key);
			}
		}
		$session->redirect($url, $http301 = false);
	}

	public static function xref($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'custID|text', 'custitemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		self::initHooks();
		$page    = self::pw('page');
		$cxm    = CxmController::getCxm();
		$xref = $cxm->get_create_xref($data->custID, $data->custitemID);
		$page->headline = "ITM: $xref->itemid CXM $xref->custid-$xref->custitemid";

		if ($xref->isNew()) {
			$xref->setItemid($data->itemID);
			$page->headline = "ITM: $xref->itemid CXM Create X-ref";
		}
		$page->js .= self::pw('config')->twig->render('items/cxm/item/form/js.twig', ['cxm' => $cxm, 'xref' => $xref]);
		$html = self::xrefDisplay($data, $xref);
		self::pw('session')->removeFor('response', 'cxm');
		return $html;
	}

	private static function xrefDisplay($data, ItemXrefCustomer $xref) {
		$itm     = self::getItm();
		$item = $itm->get_item($data->itemID);
		$cxm  = CxmController::getCxm();
		$html = '';
		$html .= self::cxmHeaders();
		$html .= CxmController::lockXref($xref);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/cxm/form/display.twig', ['xref' => $xref, 'item' => $item, 'cxm' => $cxm, 'qnotes' => self::pw('modules')->get('QnotesItemCxm'), 'customer' => $cxm->get_customer($data->custID)]);

		if (!$xref->isNew()) {
			$html .= CxmController::qnotesDisplay($xref);
		}
		return $html;
	}

	private static function cxmHeaders() {
		$html = '';
		$session = self::pw('session');
		$config  = self::pw('config');

		$html .= self::breadCrumbs();

		if ($session->getFor('response','cxm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response','cxm')]);
		}
		if ($session->response_qnote) {
			$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
			$session->remove('response_qnote');
		}
		if ($session->response_pdm) {
			$html .= $config->twig->render('mso/pdm/response-alert.twig', ['response' => $session->response_pdm]);
		}
		return $html;
	}

	public static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'q|text'];
		$data = self::sanitizeParametersShort($data, $fields);
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

		$page->js .= self::pw('config')->twig->render('items/itm/xrefs/cxm/list/js.twig');
		$html = self::listDisplay($data, $xrefs);
		self::pw('session')->removeFor('response', 'cxm');
		return $html;
	}

	private static function listDisplay($data, PropelModelPager $xrefs) {
		$itm     = self::getItm();
		$item = $itm->get_item($data->itemID);

		$html = '';
		$html .= self::cxmHeaders();
		$html .= self::pw('config')->twig->render('items/itm/xrefs/cxm/list/display.twig', ['item' => $item, 'cxm' => CxmController::getCxm(), 'items' => $xrefs]);
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
		$m = CxmController::getCxm();

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
