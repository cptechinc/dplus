<?php namespace Controllers\Mso\Somain;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use ItemXrefCustomer;
// Dplus Filters
use Dplus\Filters;
use Dplus\Filters\Mso\Cxm as CxmFilter;
// Dplus Qnotes
use Dplus\Qnotes;
// Dplus X-Refs
use Dplus\Xrefs\Cxm as XrefManager;

class Cxm extends AbstractController {
	const DPLUSPERMISSION = 'cxm';
	const TITLE = 'Customer Item X-Ref';
	const SUMMARY = 'View / Edit Customer Item X-Refs';

	private static $cxm;

	public static function index($data) {
		$fields = ['custID|text', 'custitemID|text', 'q|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = 'Customer Item X-Ref';

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}
		self::initHooks();

		if (empty($data->custID) === false) {
			if (empty($data->custitemID) === false) {
				return self::xref($data);
			}
			return self::listCustXrefs($data);
		}
		return self::listCustomers($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'custID|text', 'custitemID|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateUserPermission() === false) {
			self::pw('session')->redirect(self::url(), $http301 = false);
		}
		$cxm = self::getCxm();

		if ($data->action) {
			if (strpos($data->action, 'notes') !== false) {
				self::getQnotes()->processInput(self::pw('input'));
			} else {
				$cxm->processInput(self::pw('input'));
			}
		}
		$url = self::custUrl($data->custID);

		if ($cxm->exists($data->custID, $data->custitemID)) {
			$url = self::xrefUrl($data->custID, $data->custitemID);
			$response = $cxm->getResponse();

			if ($response && $response->hasSuccess()) {
				$url = self::xrefListUrl($data->custID, $response->key);
			}
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function xref($data) {
		$fields = ['custID|text', 'custitemID|text', 'itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$config  = self::pw('config');
		$page    = self::pw('page');
		$cxm     = self::getCxm();;
		$xref    = $cxm->getOrCreateXref($data->custID, $data->custitemID, $data->itemID);
		
		$page->headline = "CXM: New X-Ref";

		if ($xref->isNew() === false) {
			$cxm->lockrecord($xref);
			$page->headline = "CXM: $xref->itemid";
		}

		$page->js .= $config->twig->render('items/cxm/.new/xref/.js.twig', ['cxm' => $cxm, 'xref' => $xref]);

		if ($xref->isNew() === false && $cxm->recordlocker->userHasLocked($cxm->getRecordlockerKey($xref))) {
			$qnotes = self::getQnotes();
			$page->js .= $config->twig->render('items/cxm/.new/xref/qnotes/.js.twig', ['qnotes' => $qnotes]);
			$page->js .= $config->twig->render('msa/noce/ajax/js.twig');
		}
		$html = self::displayXref($data, $xref);
		$cxm->deleteResponse();
		return $html;
	}

	private static function listCustomers($data) {
		self::sanitizeParametersShort($data, ['q|text', 'orderby|text']);
		Filters\SortFilter::removeFromSession('customer', 'cxm');

		$page    = self::pw('page');
		$cxm     = self::getCxm();
		$cxm->recordlocker->deleteLock();

		$filter = new Filters\Mar\Customer();
		$filter->custid($cxm->custids());

		if ($data->q) {
			// $page->headline = "CXM: Searching Customers for '$data->q'";
			$filter->search($data->q);
		}

		if (empty($data->q) === false || empty($data->orderby) === false) {
			$sortFilter = Filters\SortFilter::fromArray(['q' => $data->q, 'orderby' => $data->orderby]);
			$sortFilter->saveToSession('customer', 'cxm');
		}
		$filter->sortby($page);
		$customers = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		$page->js .= self::pw('config')->twig->render('items/cxm/.new/customers/.js.twig');
		$html = self::displayCustomerList($data, $customers);
		$cxm->deleteResponse();
		return $html;
	}

	private static function listCustXrefs($data) {
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
			// $page->headline = "CXM: searching $customer->name X-Refs '$data->q'";
			$filter->search($data->q);
		}

		if (empty($data->q) === false || empty($data->orderby) === false) {
			$sortFilter = Filters\SortFilter::fromArray(['q' => $data->q, 'orderby' => $data->orderby]);
			$sortFilter->saveToSession('xrefs', 'cxm');
		}
		
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		
		$page->js .= self::pw('config')->twig->render('items/cxm/.new/customer/xrefs/.js.twig');
		$html = self::displayCustXrefs($data, $xrefs);
		$cxm->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayCustomerList($data, PropelModelPager $customers) {
		$config  = self::pw('config');
		$html = '';
		$html .= self::renderCxmHeader();
		$html .= self::renderCustomerList($data, $customers);
		return $html;
	}

	private static function displayCustXrefs($data, PropelModelPager $xrefs) {
		$html = '';
		$html .= self::renderCxmHeader();
		$html .= self::renderCustomerXrefsList($data, $xrefs);
		return $html;
	}


	private static function displayXref($data, ItemXrefCustomer $xref) {
		$html = '';
		$html .= self::renderCxmHeader($xref);
		$html .= self::renderXrefIsLockedAlert($xref);
		$html .= self::renderXref($data, $xref);
		return $html;
	}


/* =============================================================
	Render HTML
============================================================= */
	private static function renderCxmHeader(ItemXrefCustomer $xref = null) {
		$html = '';
		
		$html .= self::pw('config')->twig->render('items/cxm/bread-crumbs.twig', ['xref' => $xref]);
		$html .= self::renderCxmResponses();
		return $html;
	}

	/**
	 * 
	 *	NOTE: Keep public for ITM
	 * @return string
	 */
	public static function renderCxmResponses() {
		$config   = self::pw('config');
		$session  = self::pw('session');
		$response = self::getCxm()->getResponse();

		$html = '';

		if ($response && $response->hasError()) {
			$html .= $config->twig->render('items/cxm/.new/response.twig', ['response' => $response]);
		}

		$qnoteResponse = self::getQnotes()->getResponse();

		if ($qnoteResponse && $qnoteResponse->hasError()) {
			$html .= $config->twig->render('items/cxm/.new/response.twig', ['response' => $qnoteResponse]);
			self::getQnotes()->deleteResponse();
		}
		if ($session->response_pdm) {
			$html .= $config->twig->render('mso/pdm/response-alert.twig', ['response' => $session->response_pdm]);
		}
		return $html;
	}

	/**
	 * 
	 *	NOTE: Keep public for ITM
	 * @return string
	 */
	public static function renderXrefIsLockedAlert(ItemXrefCustomer $xref) {
		if ($xref->isNew()) {
			return '';
		}

		$cxm = self::getCxm();
		if ($cxm->lockrecord($xref)) {
			return '';
		}
		$msg = "CXM ". $cxm->getRecordlockerKey($xref) ." is being locked by " . $cxm->recordlocker->getLockingUser($cxm->getRecordlockerKey($xref));
		$alert = self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "CXM ".$cxm->getRecordlockerKey($xref)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		$html  = '<div class="mb-3">' . $alert . '</div>';
		return $html;
	}
	
	private static function renderCustomerList($data, PropelModelPager $customers) {
		return self::pw('config')->twig->render('items/cxm/.new/customers/display.twig', ['customers' => $customers]);
	}

	private static function renderCustomerXrefsList($data, PropelModelPager $xrefs) {
		$cxm = self::getCxm();
		return self::pw('config')->twig->render('items/cxm/.new/customer/xrefs/display.twig', ['cxm' => $cxm, 'xrefs' => $xrefs]);
	}

	private static function renderXref($data, ItemXrefCustomer $xref) {
		$cxm    = self::getCxm();
		$qnotes = self::getQnotes();
		return self::pw('config')->twig->render('items/cxm/.new/xref/display.twig', ['xref' => $xref, 'cxm' => $cxm,  'qnotes' => $qnotes]);
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function url() {
		return Menu::cxmUrl();
	}
	
	/**
	 * Return URL for Vxm Customer
	 * @param  string $custID  Customer ID
	 * @return string
	 */
	public static function custUrl($custID) {
		$url = new Purl(Menu::cxmUrl());
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
		$xref = self::getCxm()->xrefByRecordlockerKey($focus);

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

		$pagenbr = self::getPagenbrFromOffset($offset);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'cxm', $pagenbr);

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
			return self::url();
		}
		return self::custListFocusUrl($custID);
	}

	public static function custListFocusUrl($custID) {
		$url = new Purl(self::url());
		$cxm = self::getCxm();
		$filter = new Filters\Mar\Customer();

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
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'cxm', $pagenbr);
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
		$url = new Purl(Menu::cxmUrl());
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
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=somain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=somain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook("Page(pw_template=somain)::custUrl", function($event) {
			$custID = $event->arguments(0); // To focus on
			$event->return = self::custUrl($custID);
		});

		$m->addHook("Page(pw_template=somain)::custListUrl", function($event) {
			$custID = $event->arguments(0); // To focus on
			$event->return = self::custListUrl($custID);
		});

		$m->addHook('Page(pw_template=somain)::xrefExitUrl', function($event) {
			$p = $event->object;
			$xref = $event->arguments(0); // Xref
			$cxm  = self::getCxm();
			$event->return = $xref ? self::xrefListUrl($xref->custid, $cxm->getRecordlockerKey($xref)) : self::custUrl($p->wire('input')->get->text('custID'));
		});

		$m->addHook('Page(pw_template=somain)::xrefUrl', function($event) {
			$custID     = $event->arguments(0);
			$custitemID = $event->arguments(1);
			$event->return = self::xrefUrl($custID, $custitemID);
		});

		$m->addHook('Page(pw_template=somain)::xrefNewUrl', function($event) {
			$custID     = $event->arguments(0);
			$event->return = self::xrefUrl($custID, 'new');
		});

		$m->addHook('Page(pw_template=somain)::xrefDeleteUrl', function($event) {
			$custID     = $event->arguments(0);
			$custitemID = $event->arguments(1);
			$event->return = self::xrefDeleteUrl($custID, $custitemID);
		});
	}

/* =============================================================
	Supplmental Functions
============================================================= */
	public static function getCxm() {
		if (empty(self::$cxm)) {
			self::$cxm = XrefManager::instance();
		}
		return self::$cxm;
	}

	public static function getQnotes() {
		return Qnotes\Icxm::instance();
	}
}
