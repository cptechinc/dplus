<?php namespace Controllers\Map\Apmain;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemXrefManufacturer;
use Vendor;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefMxrfe as MxrfeCRUD, ProcessWire\WireInput;
// Dplus Filters
use Dplus\Filters\Map\Mxrfe  as MxrfeFilter;
use Dplus\Filters\Map\Vendor as VendorFilter;

class Mxrfe extends AbstractController {
	const DPLUSPERMISSION = 'mxrfe';
	private static $mxrfe;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['mnfrID|string', 'mnfritemID|string', 'q|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;
		$page->title = 'Manufacturer Item X-Ref';

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		self::initHooks();

		if (empty($data->mnfrID) === false) {
			if (empty($data->mnfritemID) === false) {
				return self::xref($data);
			}
			return self::mnfrXrefs($data);
		}
		return self::listMnfrs($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text'];
		self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if (self::validateUserPermission() === false) {
			self::pw('session')->redirect(self::url(), $http301 = false);
		}

		if ($data->action) {
			$mxrfe = self::mxrfeMaster();
			$mxrfe->process_input($input);
		}
		self::pw('session')->redirect(self::mxrfeRedirUrl($input), $http301 = false);
	}

	private static function xref($data) {
		$fields = ['mnfrID|string', 'mnfritemID|string', 'itemID|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if ($data->action) {
			return self::handleCRUD($data);
		}

		$mxrfe  = self::mxrfeMaster();
		$xref   = $mxrfe->get_create_xref($data->mnfrID, $data->mnfritemID, $data->itemID);
		$page   = self::pw('page');

		if ($xref->isNew()) {
			$page->headline = "MXRFE: New X-Ref";
		}
		if ($xref->isNew() === false) {
			$page->headline = "MXRFE: $xref->itemid";
		}
		$page->js   .= self::pw('config')->twig->render('items/mxrfe/xref/form/.js.twig', ['mxrfe' => $mxrfe, 'xref' => $xref]);
		$html = self::displayXref($data, $xref);
		$mxrfe->deleteResponse();
		return $html;
	}

	public static function listMnfrs($data) {
		self::sanitizeParametersShort($data, ['q|text']);
		$page   = self::pw('page');
		$page->show_breadcrumbs = false;
		$mxrfe  = self::mxrfeMaster();
		$mxrfe->recordlocker->deleteLock();
		$filter = new VendorFilter();
		$filter->init();
		$filter->vendorid($mxrfe->vendorids());
		if ($data->q) {
			// $page->headline = "MXRFE: Searching Mnfrs for '$data->q'";
			$filter->search($data->q);
		}
		$filter->sort(self::pw('input')->get);
		$filter->query->orderBy(Vendor::aliasproperty('id'));
		$vendors = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$page->js .= self::pw('config')->twig->render('items/mxrfe/search/vendor/js.twig');
		$html = self::displayListMnfrs($data, $vendors);
		$mxrfe->deleteResponse();
		return $html;
	}

	public static function mnfrXrefs($data) {
		self::sanitizeParametersShort($data, ['mnfrID|string']);
		$page   = self::pw('page');
		$mxrfe  = self::mxrfeMaster();
		$mxrfe->recordlocker->deleteLock();

		$filter = new MxrfeFilter();
		$filter->vendorid($data->mnfrID);
		$filter->sortby($page);
		$page->headline = "MXRFE: $data->mnfrID";
		if ($data->q) {
			// $page->headline = "MXRFE: Searching $data->mnfrID X-Refs for '$data->q'";
			$filter->search($data->q);
		}
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$html  = self::displayMnfrXrefs($data, $xrefs);
		$mxrfe->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayXref($data, ItemXrefManufacturer $xref) {
		$config = self::pw('config');
		$mxrfe  = self::mxrfeMaster();
		$mxrfe->init_field_attributes_config();
		$vendor = $mxrfe->vendor($data->mnfrID);
		$qnotes = self::pw('modules')->get('QnotesItemMxrfe');

		$html = '';
		$html .= self::mxrfeHeaders($xref);
		$html .= self::lockXref($xref);
		$html .= $config->twig->render('items/mxrfe/xref/form/display.twig', ['mxrfe' => $mxrfe, 'vendor' => $vendor, 'xref' => $xref, 'qnotes' => $qnotes]);

		if ($xref->isNew() === false && $mxrfe->recordlocker->userHasLocked($mxrfe->get_recordlocker_key($xref))) {
			$html .= self::qnotesDisplay($xref);
		}
		return $html;
	}

	public static function lockXref(ItemXrefManufacturer $xref) {
		$html = '';
		$mxrfe = self::mxrfeMaster();

		if (!$xref->isNew()) {
			if (!$mxrfe->lockrecord($xref)) {
				$msg = "MXRFE ". $mxrfe->get_recordlocker_key($xref) ." is being locked by " . $mxrfe->recordlocker->getLockingUser($mxrfe->get_recordlocker_key($xref));
				$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "MXRFE ".$mxrfe->get_recordlocker_key($xref)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$html .= '<div class="mb-3"></div>';
			}
		}
		return $html;
	}

	public static function qnotesDisplay(ItemXrefManufacturer $xref) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$qnotes = self::pw('modules')->get('QnotesItemMxrfe');
		$html = '<hr> <div class="mt-3"></div>';
		$html .= $config->twig->render('items/mxrfe/xref/notes/notes.twig', ['xref' => $xref, 'qnotes' => $qnotes]);
		$page->js   .= $config->twig->render('items/mxrfe/xref/notes/js.twig', ['xref' => $xref, 'qnotes' => $qnotes]);
		self::pw('session')->remove('response_qnote');
		return $html;
	}

	private static function mxrfeHeaders(ItemXrefManufacturer $xref = null) {
		$html = '';
		$session = self::pw('session');
		$config  = self::pw('config');

		$html .= $config->twig->render('items/mxrfe/bread-crumbs.twig', ['xref' => $xref]);

		$response = $session->getFor('response','mxrfe');
		if (empty($response)) {
			return $html;
		}
		if ($response->has_success()) {
			return $html;
		}
		$html .= $config->twig->render('items/cxm/response.twig', ['response' => $response]);
		return $html;
	}

	private static function displayListMnfrs($data, PropelModelPager $vendors) {
		$config = self::pw('config');

		$html = '';
		$html .= self::mxrfeHeaders();
		$html .= $config->twig->render('items/mxrfe/search/vendor/page.twig', ['vendors' => $vendors]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $vendors]);
		$html .= $config->twig->render('items/mxrfe/new-xref-modal.twig');
		return $html;
	}

	private static function displayMnfrXrefs($data, $xrefs) {
		$qnotes = self::pw('modules')->get('QnotesItemMxrfe');
		$mxrfe  = self::mxrfeMaster();
		$vendor = $mxrfe->vendor($data->mnfrID);

		$html = '';
		$html .= self::mxrfeHeaders();
		$html .= self::pw('config')->twig->render('items/mxrfe/list/vendor/display.twig', ['mxrfe' => $mxrfe, 'xrefs' => $xrefs, 'vendor' => $vendor, 'qnotes' => $qnotes]);
		return $html;
	}

/* =============================================================
	Masters
============================================================= */
	public static function mxrfeMaster() {
		if (empty(self::$mxrfe)) {
			self::$mxrfe = self::pw('modules')->get('XrefMxrfe');
		}
		return self::$mxrfe;
	}

/* =============================================================
	URL Functions
============================================================= */
	/**
	 * Return URL to MXRFE
	 * @return string
	 */
	public static function url() {
		return Menu::mxrfeUrl();
	}

	/**
	 * Return URL to MXRFE X-Ref
	 * @param  string $mnfrID      Manufacturer ID
	 * @param  string $mnfritemID  Vendor Item ID
	 * @param  string $itemID        ITM Item ID
	 * @return string
	 */
	public static function xrefUrl($mnfrID, $mnfritemID, $itemID) {
		$url = new Purl(Menu::mxrfeUrl());
		$url->query->set('mnfrID', $mnfrID);
		$url->query->set('mnfritemID', $mnfritemID);
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}

	/**
	 * Return URL to DELETE MXRFE X-Ref
	 * @param  string $mnfrID      Manufacturer ID
	 * @param  string $mnfritemID  Vendor Item ID
	 * @param  string $itemID      ITM Item ID
	 * @return string
	 */
	public static function xrefDeleteUrl($mnfrID, $mnfritemID, $itemID) {
		$url = new Purl(self::xrefUrl($mnfrID, $mnfritemID, $itemID));
		$url->query->set('action', 'delete-xref');
		return $url->getUrl();
	}

	/**
	 * Return URL to MXRFRE Manufacturer X-Ref List
	 * @param  string $mnfrID  Manufacturer ID
	 * @return string
	 */
	public static function _mnfrUrl($mnfrID) {
		$url = new Purl(Menu::mxrfeUrl());
		$url->query->set('mnfrID', $mnfrID);
		return $url->getUrl();
	}

	/**
	 * Return URL to MXRFRE Manufacturer X-Ref List
	 * @param  string $mnfrID Manufacturer ID
	 * @return string
	 */
	public static function mnfrUrl($mnfrID, $focus = '') {
		if ($focus == '') {
			return self::_mnfrUrl($mnfrID);
		}
		return self::mnfrFocusUrl($mnfrID, $focus);
	}

	/**
	 * Return Paginated URL to MXRFE Vendor Page
	 * @param  string $mnfrID Mnfr / Comp ID
	 * @param  string $focus    Record Locker Key for X-Ref
	 * @return string
	 */
	public static function mnfrFocusUrl($mnfrID, $focus) {
		$mxrfe = self::mxrfeMaster();
		$xref  = $mxrfe->xref_by_recordlocker_key($focus);
		if ($xref == false) {
			return self::_mnfrUrl($mnfrID);
		}
		$url = new Purl(self::_mnfrUrl($mnfrID));
		$url->query->set('focus', $focus);
		$filter = new MxrfeFilter();
		$filter->vendorid($mnfrID);
		$position = $filter->position($xref);
		$pagenbr = self::getPagenbrFromOffset($position);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'mxrfe', $pagenbr);
		return $url->getUrl();
	}

	/**
	 * Return URL to MXRFE Manufacturer list
	 * @param  string $mnfrID Manufacturer ID to focus
	 * @return string
	 */
	public static function mnfrListUrl($mnfrID = '') {
		if (empty($mnfrID)) {
			return self::_mnfrListUrl();
		}
		return self::mnfrListFocusUrl($mnfrID);
	}

	/**
	 * Return URL to MXRFE Manufacturer list
	 * @param  string $mnfrID Manufacturer ID to focus
	 * @return string
	 */
	public static function mnfrListFocusUrl($mnfrID) {
		$filter = new VendorFilter();
		$filter->init();
		if ($filter->exists($mnfrID) === false) {
			return self::_mnfrListUrl();
		}
		$filter->vendorid(self::mxrfeMaster()->vendorids());
		$position = $filter->positionById($mnfrID);
		$pagenbr = self::getPagenbrFromOffset($position);

		$url = new Purl(self::_mnfrListUrl());
		$url->query->set('focus', $mnfrID);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'mxrfe', $pagenbr);
		return $url->getUrl();
	}

	/**
	 * Return URL to MXRFRE Manufacturer list
	 * @return string
	 */
	public static function _mnfrListUrl() {
		return Menu::mxrfeUrl();
	}

	/**
	 * Return URL to MXRFE X-Ref
	 * @param  string $mnfrID      Manufacturer ID
	 * @param  string $mnfritemID  Vendor Item ID
	 * @param  string $itemID      ITM Item ID
	 * @return string
	 */
	public static function mxrfeRedirUrl(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if (empty($values->string('mnfrID')) && $values->text('action') != 'update-notes' && $values->text('action') != 'delete-notes') {
			return Menu::mxrfeUrl();
		}

		$mnfrID     = $values->string('mnfrID');
		$mnfritemID = $values->string('mnfritemID');
		$itemID     = $values->string('itemID');
		$mxrfe = self::mxrfeMaster();

		if (in_array($values->text('action'), ['delete-xref', 'update-notes', 'delete-notes']) === false) {
			if ($mxrfe->xref_exists($mnfrID, $mnfritemID, $itemID) === false) {
				return self::pw('pages')->get('pw_template=apmain')->url;
			}
		}

		switch ($values->text('action')) {
			case 'update-xref':
				$xref = $mxrfe->xref($mnfrID, $mnfritemID, $itemID);
				$focus = $mxrfe->get_recordlocker_key($xref);
				return self::mnfrFocusUrl($mnfrID, $focus);
				break;
			case 'delete-xref':
				return self::mnfrUrl($mnfrID);
				break;
			case 'delete-notes':
			case 'update-notes';
				if (strtolower($values->text('type')) == 'intv') {
					$mnfrID     = $values->string('vendorID');
					$mnfritemID = $values->string('vendoritemID');
				}
				return self::xrefUrl($mnfrID, $mnfritemID, $itemID);
				break;
		}
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=apmain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=apmain)::mnfrUrl', function($event) {
			$event->return = self::mnfrUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=apmain)::xrefUrl', function($event) {
			$mnfrID        = $event->arguments(0);
			$mnfritemID    = $event->arguments(1);
			$itemID        = $event->arguments(2);
			$event->return = self::xrefUrl($mnfrID, $mnfritemID, $itemID);
		});

		$m->addHook('Page(pw_template=apmain)::xrefExitUrl', function($event) {
			$m = self::pw('modules')->get('XrefMxrfe');
			$p = $event->object;
			$xref = $event->arguments(0);
			$event->return = $xref ? self::mnfrFocusUrl($xref->vendorid, $m->get_recordlocker_key($xref)) : self::mnfrUrl($p->wire('input')->get->text('mnfrID'));
			$event->return = self::mnfrUrl($xref->vendorid, $m->get_recordlocker_key($xref));
		});

		$m->addHook('Page(pw_template=apmain)::xrefDeleteUrl', function($event) {
			$mnfrID        = $event->arguments(0);
			$mnfritemID    = $event->arguments(1);
			$itemID        = $event->arguments(2);
			$event->return = self::xrefDeleteUrl($mnfrID, $mnfritemID, $itemID);
		});

		$m->addHook('Page(pw_template=apmain)::mnfrListUrl', function($event) {
			$event->return = self::mnfrListUrl($event->arguments(0));
		});
	}
}
