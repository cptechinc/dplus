<?php namespace Controllers\Map;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemXrefManufacturer;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefMxrfe as MxrfeCRUD;
// Dplus Filters
use Dplus\Filters\Map\Mxrfe  as MxrfeFilter;
use Dplus\Filters\Map\Vendor as VendorFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Mxrfe extends AbstractController {
	private static $mxrfe;

	public static function index($data) {
		$fields = ['mnfrID|text', 'mnfritemID|text', 'q|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

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
		$data = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if ($data->action) {
			$mxrfe = self::mxrfeMaster();
			$mxrfe->process_input($input);
		}
		self::pw('session')->redirect(self::mxrfeRedirUrl($input), $http301 = false);
	}

	public static function xref($data) {
		$fields = ['mnfrID|text', 'mnfritemID|text', 'itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if ($data->action) {
			return self::handleCRUD($data);
		}

		$mxrfe  = self::mxrfeMaster();
		$xref   = $mxrfe->get_create_xref($data->mnfrID, $data->mnfritemID, $data->itemID);
		$page   = self::pw('page');

		if ($xref->isNew()) {
			$page->headline = "MXRFE: New X-ref";
		}
		if ($xref->isNew() === false) {
			$page->headline = "MXRFE: " . $mxrfe->get_recordlocker_key($xref);
		}
		$page->js   .= self::pw('config')->twig->render('items/mxrfe/item/form/js.twig', ['mxrfe' => $mxrfe, 'xref' => $xref]);
		$html = self::xrefDisplay($data, $xref);
		return $html;
	}

	private static function xrefDisplay($data, ItemXrefManufacturer $xref) {
		$config = self::pw('config');
		$mxrfe  = self::mxrfeMaster();
		$mxrfe->init_field_attributes_config();
		$vendor = $mxrfe->vendor($data->mnfrID);
		$qnotes = self::pw('modules')->get('QnotesItemMxrfe');

		$html = '';
		$html .= self::mxrfeHeaders();
		$html .= self::lockXref($xref);
		$html .= $config->twig->render('items/mxrfe/item/form/display.twig', ['mxrfe' => $mxrfe, 'vendor' => $vendor, 'xref' => $xref, 'qnotes' => $qnotes]);

		if (!$xref->isNew()) {
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
		$html .= $config->twig->render('items/mxrfe/item/notes/notes.twig', ['xref' => $xref, 'qnotes' => $qnotes]);
		$page->js   .= $config->twig->render('items/mxrfe/item/notes/js.twig', ['xref' => $xref, 'qnotes' => $qnotes]);
		self::pw('session')->remove('response_qnote');
		return $html;
	}

	private static function mxrfeHeaders() {
		$html = '';
		$session = self::pw('session');
		$config  = self::pw('config');

		$html .= $config->twig->render('items/mxrfe/bread-crumbs.twig');

		if ($session->getFor('response','mxrfe')) {
			$html .= $config->twig->render('items/cxm/response.twig', ['response' => $session->getFor('response','mxrfe')]);
		}
		return $html;
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['mnfrID|text']);
		if ($data->mnfrID) {
			return self::mnfrXrefs($data);
		}
		return self::listMnfrs($data);
	}

	public static function listMnfrs($data) {
		$data   = self::sanitizeParametersShort($data, ['q|text']);
		$page   = self::pw('page');
		$page->show_breadcrumbs = false;
		$mxrfe  = self::mxrfeMaster();
		$mxrfe->recordlocker->deleteLock();
		$filter = new VendorFilter();
		$filter->init();
		$filter->vendorid($mxrfe->vendorids());
		if ($data->q) {
			$page->headline = "Searching Mnfrs for '$data->q'";
			$filter->search($data->q);
		}
		$filter->sortby($page);
		$vendors = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		$html = self::listMnfrsDisplay($data, $vendors);
		return $html;
	}

	private static function listMnfrsDisplay($data, PropelModelPager $vendors) {
		$config = self::pw('config');

		$html = '';
		$html .= self::mxrfeHeaders();
		$html .= $config->twig->render('items/mxrfe/search/vendor/page.twig', ['vendors' => $vendors]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $vendors]);
		return $html;
	}

	public static function mnfrXrefs($data) {
		$data = self::sanitizeParametersShort($data, ['mnfrID|text']);
		$page   = self::pw('page');
		$mxrfe  = self::mxrfeMaster();
		$mxrfe->recordlocker->deleteLock();

		$filter = new MxrfeFilter();
		$filter->vendorid($data->mnfrID);
		$filter->sortby($page);
		$page->headline = "MXRFE: Mnfr $data->mnfrID";
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$html  = self::mnfrXrefsDisplay($data, $xrefs);
		return $html;
	}

	private static function mnfrXrefsDisplay($data, $xrefs) {
		$mxrfe  = self::mxrfeMaster();
		$vendor = $mxrfe->vendor($data->mnfrID);

		$html = '';
		$html .= self::mxrfeHeaders();
		$html .= self::pw('config')->twig->render('items/mxrfe/list/vendor/display.twig', ['mxrfe' => $mxrfe, 'xrefs' => $xrefs, 'vendor' => $vendor]);
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
	 * Return URL to MXRFE X-ref
	 * @param  string $mnfrID      Vendor ID
	 * @param  string $mnfritemID  Vendor Item ID
	 * @param  string $itemID        ITM Item ID
	 * @return string
	 */
	public static function xrefUrl($mnfrID, $mnfritemID, $itemID) {
		$url = new Purl(self::pw('pages')->get('pw_template=mxrfe')->url);
		$url->query->set('mnfrID', $mnfrID);
		$url->query->set('mnfritemID', $mnfritemID);
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}

	/**
	 * Return URL to DELETE MXRFE X-ref
	 * @param  string $mnfrID      Vendor ID
	 * @param  string $mnfritemID  Vendor Item ID
	 * @param  string $itemID        ITM Item ID
	 * @return string
	 */
	public function xrefDeleteUrl($mnfrID, $mnfritemID, $itemID) {
		$url = new Purl(self::xrefUrl($mnfrID, $mnfritemID, $itemID));
		$url->query->set('action', 'delete-xref');
		return $url->getUrl();
	}

	/**
	 * Return URL to MXRFRE vendor list
	 * @param  string $mnfrID VendorID
	 * @return string
	 */
	public static function _mnfrUrl($mnfrID) {
		$url = new Purl(self::pw('pages')->get('pw_template=mxrfe')->url);
		$url->query->set('mnfrID', $mnfrID);
		return $url->getUrl();
	}

	/**
	 * Return URL to MXRFRE vendor list
	 * @param  string $mnfrID VendorID
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
		$pagenbr = ceil($position / self::pw('session')->display);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, self::pw('pages')->get('pw_template=mxrfe')->name, $pagenbr);
		return $url->getUrl();
	}

	/**
	 * Return URL to MXRFE X-ref
	 * @param  string $mnfrID      Vendor ID
	 * @param  string $mnfritemID  Vendor Item ID
	 * @param  string $itemID      ITM Item ID
	 * @return string
	 */
	public static function mxrfeRedirUrl(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if (empty($values->text('mnfrID'))) {
			return self::pw('pages')->get('pw_template=mxrfe')->url;
		}

		$mnfrID     = $values->text('mnfrID');
		$mnfritemID = $values->text('mnfritemID');
		$itemID     = $values->text('itemID');
		$mxrfe = self::mxrfeMaster();

		if ($mxrfe->xref_exists($mnfrID, $mnfritemID, $itemID) === false) {
			return self::pw('pages')->get('pw_template=mxrfe')->url;
		}

		$xref = $mxrfe->xref($mnfrID, $mnfritemID, $itemID);

		switch ($values->text('action')) {
			case 'update-xref':
				$focus = $mxrfe->get_recordlocker_key($xref);
				return self::mnfrFocusUrl($mnfrID, $focus);
				break;
			case 'delete-xref':
				return self::mnfrUrl($mnfrID);
				break;
			case 'delete-notes':
			case 'update-notes';
				return self::xrefUrl($mnfrID, $mnfritemID, $itemID);
				break;
		}
	}

	public static function init() {
		$m = self::pw('modules')->get('XrefMxrfe');

		$m->addHook('Page(pw_template=mxrfe)::mnfrUrl', function($event) {
			$mnfrID        = $event->arguments(0);
			$event->return = self::mnfrUrl($mnfrID);
		});

		$m->addHook('Page(pw_template=mxrfe)::xrefUrl', function($event) {
			$mnfrID        = $event->arguments(0);
			$mnfritemID    = $event->arguments(1);
			$itemID        = $event->arguments(2);
			$event->return = self::xrefUrl($mnfrID, $mnfritemID, $itemID);
		});

		$m->addHook('Page(pw_template=mxrfe)::xrefExitUrl', function($event) {
			$m = self::pw('modules')->get('XrefMxrfe');
			$xref = $event->arguments(0);
			$event->return = self::mnfrUrl($xref->vendorid, $m->get_recordlocker_key($xref));
		});

		$m->addHook('Page(pw_template=mxrfe)::xrefDeleteUrl', function($event) {
			$mnfrID        = $event->arguments(0);
			$mnfritemID    = $event->arguments(1);
			$itemID        = $event->arguments(2);
			$event->return = self::xrefDeleteUrl($mnfrID, $mnfritemID, $itemID);
		});
	}
}
