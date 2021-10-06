<?php namespace Controllers\Mso;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use CustomerQuery;
use ItemXrefCustomer;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefCxm as CxmCRUD;
// Dplus Filters
use Dplus\Filters;
use Dplus\Filters\Mso\Cxm as CxmFilter;
use Dplus\Filters\Mar\Customer as CustomerFilter;

// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Cxm extends AbstractController {
	private static $cxm;

	public static function index($data) {
		$fields = ['custID|text', 'custitemID|text', 'q|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->custID) === false) {
			if (empty($data->custitemID) === false) {
				return self::xref($data);
			}
			return self::custXrefs($data);
		}
		return self::listCustomers($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'custID|text', 'custitemID|text'];
		self::sanitizeParametersShort($data, $fields);

		if ($data->action) {
			$cxm = self::getCxm();
			$cxm->process_input(self::pw('input'));
		}
		$session = self::pw('session');

		$response = $session->getFor('response', 'cxm');
		$url      = self::custUrl($data->custID);

		if ($cxm->xref_exists($data->custID, $data->custitemID)) {
			$url = self::xrefUrl($data->custID, $data->custitemID);

			if ($response  && $response ->has_success()) {
				$url = self::xrefListUrl($data->custID, $response->key);
			}
		}
		$session->redirect($url, $http301 = false);
	}

	public static function xref($data) {
		$fields = ['custID|text', 'custitemID|text', 'itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$config  = self::pw('config');
		$page    = self::pw('page');
		$cxm     = self::getCxm();;
		$xref    = $cxm->get_create_xref($data->custID, $data->custitemID, $data->itemID);

		if ($xref->isNew()) {
			$page->headline = "CXM: New X-Ref";
		}
		if ($xref->isNew() === false) {
			$page->headline = "CXM: $xref->custid $xref->custitemid";
		}
		$pages = self::pw('pages');
		$page->searchcustomersURL = $page->searchLookupUrl('customers');
		$page->searchitemsURL     = $pages->get('pw_template=itm-search')->url;
		$page->js .= $config->twig->render('items/cxm/xref/form/js.twig', ['cxm' => $cxm, 'xref' => $xref]);

		$html = self::xrefDisplay($data, $xref);
		return $html;
	}

	private static function xrefDisplay($data, ItemXrefCustomer $xref) {
		$config = self::pw('config');
		$cxm    = self::getCxm();

		$html = '';
		$html .= self::cxmHeaders();
		$html .= self::lockXref($xref);
		$html .= $config->twig->render('items/cxm/xref/form/display.twig', ['item' => $xref, 'cxm' => $cxm, 'qnotes' => self::pw('modules')->get('QnotesItemCxm')]);
		if ($xref->isNew() === false && $cxm->recordlocker->userHasLocked($cxm->get_recordlocker_key($xref))) {
			$html .= self::qnotesDisplay($xref);
		}
		return $html;
	}

	public static function qnotesDisplay(ItemXrefCustomer $xref) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$qnotes = self::pw('modules')->get('QnotesItemCxm');
		$html = '';
		$html .= '<div class="mt-3"><h3>Notes</h3></div>';
		$html .= $config->twig->render('items/cxm/xref/notes/qnotes.twig', ['item' => $xref, 'qnotes' => $qnotes]);
		$page->js .= $config->twig->render('items/cxm/xref/notes/js.twig', ['qnotes' => $qnotes]);
		$page->js .= $config->twig->render('msa/noce/ajax/js.twig', ['qnotes' => $qnotes]);
		return $html;
	}

	private static function cxmHeaders() {
		$html = '';
		$session = self::pw('session');
		$config  = self::pw('config');

		$html .= $config->twig->render('items/cxm/bread-crumbs.twig');
		if ($session->getFor('response','cxm')) {
			$html .= $config->twig->render('items/cxm/response.twig', ['response' => $session->getFor('response','cxm')]);
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

	public static function lockXref(ItemXrefCustomer $xref) {
		$html = '';
		$cxm = self::getCxm();
		if ($xref->isNew() === false) {
			if ($cxm->lockrecord($xref) === false) {
				$msg = "CXM ". $cxm->get_recordlocker_key($xref) ." is being locked by " . $cxm->recordlocker->getLockingUser($cxm->get_recordlocker_key($xref));
				$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "CXM ".$cxm->get_recordlocker_key($xref)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$html .= '<div class="mb-3"></div>';
			}
		}
		return $html;
	}

	public static function list($data) {
		self::sanitizeParametersShort($data, ['custID|text']);
		if ($data->custID) {
			return self::custXrefs($data);
		}
		return self::listCustomers($data);
	}

	public static function listCustomers($data) {
		self::sanitizeParametersShort($data, ['q|text', 'orderby|text']);
		Filters\SortFilter::removeFromSession('customer', 'cxm');

		$page    = self::pw('page');
		$cxm     = self::getCxm();
		$cxm->recordlocker->deleteLock();

		$filter = new CustomerFilter();
		$filter->init();
		$filter->custid($cxm->custids());

		if ($data->q) {
			$page->headline = "CXM: Searching Customers for '$data->q'";
			$filter->search($data->q);
		}

		if (empty($data->q) === false || empty($data->orderby) === false) {
			$sortFilter = Filters\SortFilter::fromArray(['q' => $data->q, 'orderby' => $data->orderby]);
			$sortFilter->saveToSession('customer', 'cxm');
		}
		$filter->sortby($page);
		$customers = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		$page->js   .= self::pw('config')->twig->render('items/cxm/search/customer/js.twig');
		$html = self::listCustomersDisplay($data, $customers);
		return $html;
	}

	private static function listCustomersDisplay($data, PropelModelPager $customers) {
		$config  = self::pw('config');
		$html = '';
		$html .= self::cxmHeaders();
		$html .= $config->twig->render('items/cxm/search/customer/results.twig', ['customers' => $customers]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $customers]);
		$html .= $config->twig->render('items/cxm/new-cxm-modal.twig');
		return $html;
	}

	public static function custXrefs($data) {
		self::sanitizeParametersShort($data, ['custID|text', 'q|text', 'orderby|text']);
		Filters\SortFilter::removeFromSession('xrefs', 'cxm');
		$page = self::pw('page');
		$cxm  = self::getCxm();
		$cxm->recordlocker->deleteLock();
		$customer = $cxm->customer($data->custID);
		$filter   = new CxmFilter();
		$filter->custid($data->custID);
		$filter->sortby($page);
		$page->headline = "CXM: $customer->name";

		if ($data->q) {
			$page->headline = "CXM: searching $customer->name X-Refs '$data->q'";
			$filter->search($data->q);
		}

		if (empty($data->q) === false || empty($data->orderby) === false) {
			$sortFilter = Filters\SortFilter::fromArray(['q' => $data->q, 'orderby' => $data->orderby]);
			$sortFilter->saveToSession('xrefs', 'cxm');
		}

		$page->js .= self::pw('config')->twig->render('items/cxm/list/js.twig');
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$html = self::customerXrefsDisplay($data, $xrefs);
		return $html;
	}

	private static function customerXrefsDisplay($data, PropelModelPager $xrefs) {
		$config  = self::pw('config');
		$cxm    = self::getCxm();
		$html = '';
		$html .= self::cxmHeaders();
		$html .= $config->twig->render('items/cxm/cxm-links.twig', []);
		$html .= $config->twig->render('items/cxm/list/display.twig', ['cxm' => $cxm, 'customer' => $cxm->get_customer($data->custID), 'response' => self::pw('session')->getFor('response', 'cxm'), 'items' => $xrefs, 'custID' => $data->custID]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $xrefs]);
		return $html;
	}

	public static function getCxm() {
		if (empty(self::$cxm)) {
			self::$cxm = self::pw('modules')->get('XrefCxm');
		}
		return self::$cxm;
	}

/* =============================================================
	URL Functions
============================================================= */
	/**
	 * Return URL for Vxm Customer
	 * @param  string $custID  Customer ID
	 * @return string
	 */
	public static function custUrl($custID) {
		$url = new Purl(self::pw('pages')->get('pw_template=cxm')->url);
		$url->query->set('custID', $custID);
		return $url->getUrl();
	}

	/**
	 * Return URL to Customer X-Refs
	 * @param  string $custID  Customer ID
	 * @param  string $focus   X-Ref Key to focus on
	 * @return string
	 */
	public static function xrefListUrl($custID, $focus = '') {
		if (empty($focus)) {
			return self::_xrefListUrl($custID);
		}
		$xref = self::getCxm()->xref_by_recordlocker_key($focus);

		if (empty($xref)) {
			return self::_xrefListUrl($custID);
		}
		$url = new Purl(self::_xrefListUrl($custID));
		$url->query->set('focus', $focus);
		$sortFilter = Filters\SortFilter::getFromSession('xrefs', 'cxm');
		$filter = new CxmFilter();
		$filter->query->setIgnoreCase(false);
		$filter->custid($custID);
		if ($sortFilter) {
			$filter->applySortFilter($sortFilter);
		}
		$offset = $filter->positionQuick($xref->custid, $xref->custitemid, $xref->itemid);
		$url->query->set('offset', $offset);

		$pagenbr = self::getPagenbrFromOffset($offset);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, self::pw('pages')->get('pw_template=cxm')->name, $pagenbr);

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

	public static function _xrefListUrl($custID) {
		return self::custUrl($custID);
	}

	/**
	 * Return URL for VXM and add focus if provided
	 * @param  string $custID  Customer ID to highlight / focus
	 * @return string
	 */
	public static function custListUrl($custID = '') {
		if (empty($custID)) {
			return self::_custListUrl();
		}
		return self::custListFocusUrl($custID);
	}

	public static function _custListUrl() {
		return self::pw('pages')->get('pw_template=cxm')->url;
	}

	public static function custListFocusUrl($custID) {
		$url = new Purl(self::_custListUrl());
		$cxm = self::getCxm();
		$filter = new CustomerFilter();
		$filter->init();

		if ($filter->exists($custID) === false) {
			return $url->getUrl();
		}

		$sortFilter = Filters\SortFilter::getFromSession('customer', 'cxm');
		$filter->custid($cxm->custids());

		if ($sortFilter) {
			$filter->applySortFilter($sortFilter);
		}

		$offset = $filter->positionQuick($custID);
		$pagenbr = self::getPagenbrFromOffset($offset);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, self::pw('pages')->get('pw_template=cxm')->name, $pagenbr);
		$url->query->set('focus', $custID);

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
	 * Return URL for Vxm X-Ref
	 * @param  string $custID      Customer ID
	 * @param  string $custitemID  Customer Item ID
	 * @return string
	 */
	public static function xrefUrl($custID, $custitemID) {
		$url = new Purl(self::pw('pages')->get('pw_template=cxm')->url);
		$url->query->set('custID', $custID);
		$url->query->set('custitemID', $custitemID);
		return $url->getUrl();
	}

	/**
	 * Return URL for Vxm X-Ref Deletion
	 * @param  string $custID      Customer ID
	 * @param  string $custitemID  Customer Item ID
	 * @return string
	 */
	public static function xrefDeleteUrl($custID, $custitemID, $itemID = '') {
		$url = new Purl(self::xrefUrl($custID, $custitemID));
		$url->query->set('action', 'delete-xref');
		return $url->getUrl();
	}

/* =============================================================
	Hook Functions
============================================================= */
	public static function init() {
		$m = self::getCxm();

		$m->addHook("Page(pw_template=cxm)::custUrl", function($event) {
			$p = $event->object;
			$custID = $event->arguments(0); // To focus on
			$event->return = self::custUrl($custID);
		});

		$m->addHook("Page(pw_template=cxm)::custListUrl", function($event) {
			$p = $event->object;
			$custID = $event->arguments(0); // To focus on
			$event->return = self::custListUrl($custID);
		});

		$m->addHook('Page(pw_template=cxm)::xrefExitUrl', function($event) {
			$p = $event->object;
			$xref = $event->arguments(0); // Xref
			$cxm  = self::getCxm();
			$event->return = self::xrefListUrl($xref->custid, $cxm->get_recordlocker_key($xref));
		});

		$m->addHook('Page(pw_template=cxm)::xrefUrl', function($event) {
			$p = $event->object;
			$custID     = $event->arguments(0);
			$custitemID = $event->arguments(1);
			$event->return = self::xrefUrl($custID, $custitemID);
		});

		$m->addHook('Page(pw_template=cxm)::xrefNewUrl', function($event) {
			$p = $event->object;
			$custID     = $event->arguments(0);
			$event->return = self::xrefUrl($custID, 'new');
		});

		$m->addHook('Page(pw_template=cxm)::xrefDeleteUrl', function($event) {
			$p = $event->object;
			$custID     = $event->arguments(0);
			$custitemID = $event->arguments(1);
			$event->return = self::xrefDeleteUrl($custID, $custitemID);
		});
	}
}
