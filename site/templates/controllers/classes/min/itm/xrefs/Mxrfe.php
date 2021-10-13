<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemXrefManufacturerQuery, ItemXrefManufacturer;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefMxrfe as MxrfeCRUD;
// Dplus Filters
use Dplus\Filters\Map\Mxrfe as MxrfeFilter;
// Mvc Controllers
use Controllers\Map\Mxrfe as BaseMxrfe;

class Mxrfe extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'mnfrID|text', 'mnfritemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->mnfritemID) == false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}
		$fields = ['itemID|text', 'mnfrID|text', 'mnfritemID|text', 'action|text'];
		$data  = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');
		$mxrfe = BaseMxrfe::mxrfeMaster();

		$url = self::xrefUrl($data->mnfrID, $data->mnfritemID, $data->itemID);

		if ($data->action) {
			$mxrfe->process_input($input);
			switch ($data->action) {
				case 'delete-xref':
				case 'update-xref':
					$url = Xrefs::xrefUrlMxrfe($data->itemID);
					break;
			}
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function xref($data) {
		self::initHooks();

		$mxrfe  = BaseMxrfe::mxrfeMaster();
		$xref   = $mxrfe->get_create_xref($data->mnfrID, $data->mnfritemID, $data->itemID);
		$page   = self::pw('page');
		$page->headline = "ITM: $data->itemID MXRFE $xref->mnfrid-$xref->mnfritemid";

		if ($xref->isNew()) {
			$xref->setItemid($data->itemID);
			$page->headline = "ITM: $data->itemID MXRFE Create X-ref";
		}
		$page->js .= self::pw('config')->twig->render('items/mxrfe/xref/form/js.twig', ['mxrfe' => $mxrfe, 'xref' => $xref]);
		self::pw('session')->removeFor('response', 'mxrfe');
		return self::displayXref($data, $xref);
	}

	private static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}
		self::sanitizeParametersShort($data, ['itemID|text', 'q|text']);
		self::initHooks();

		$mxrfe = BaseMxrfe::mxrfeMaster();
		$mxrfe->recordlocker->deleteLock();

		$page  = self::pw('page');
		$page->title = "MXRFE";
		$page->headline = "ITM: $data->itemID MXRFE";

		$filter = new MxrfeFilter();
		$filter->itemid($data->itemID);
		$filter->sortby($page);
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 10);
		$html = self::displayList($data, $xrefs);
		self::pw('session')->removeFor('response', 'mxrfe');
		return $html;
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayXref($data, ItemXrefManufacturer $xref) {
		$mxrfe  = BaseMxrfe::mxrfeMaster();
		$qnotes = self::pw('modules')->get('QnotesItemMxrfe');
		$itm    = self::getItm();
		$item   = $itm->get_item($data->itemID);
		$config = self::pw('config');

		$html = '';
		$html .= self::lockItem($data->itemID);
		$html .= self::mxrfeHeaders();
		$html .= BaseMxrfe::lockXref($xref);
		$html .= $config->twig->render('items/itm/xrefs/mxrfe/form/display.twig', ['xref' => $xref, 'item' => $item, 'mxrfe' => $mxrfe, 'qnotes' => $qnotes, 'itm' => $itm]);

		if (!$xref->isNew()) {
			$html .= BaseMxrfe::qnotesDisplay($xref);
		}
		return $html;
	}

	private static function mxrfeHeaders() {
		$html = '';
		$session = self::pw('session');
		$config  = self::pw('config');

		$html .= self::breadCrumbs();

		if ($session->getFor('response','mxrfe')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response','mxrfe')]);
		}
		return $html;
	}

	private static function displayList($data, PropelModelPager $xrefs) {
		$itm  = self::getItm();
		$item = $itm->item($data->itemID);
		$html = '';
		$html .= self::lockItem($data->itemID);
		$html .= self::mxrfeHeaders();
		$html .= self::pw('config')->twig->render('items/itm/xrefs/mxrfe/list/display.twig', ['item' => $item, 'xrefs' => $xrefs, 'mxrfe' => BaseMxrfe::mxrfeMaster(), 'itm' => $itm]);
		return $html;
	}

/* =============================================================
	Url Functions
============================================================= */
	/**
	 * Return Url to X-ref
	 * @param  string $mnfrID     Vendor ID
	 * @param  string $mnfritemID Vendor Item ID
	 * @param  string $itemID       Item ID
	 * @return string
	 */
	public static function xrefUrl($mnfrID, $mnfritemID, $itemID) {
		$url = new Purl(Xrefs::xrefUrlMxrfe($itemID));
		$url->query->set('mnfrID', $mnfrID);
		$url->query->set('mnfritemID', $mnfritemID);
		return $url->getUrl();
	}

	/**
	 * Return Url to Delete X-ref
	 * @param  string $mnfrID     Vendor ID
	 * @param  string $mnfritemID Vendor Item ID
	 * @param  string $itemID       Item ID
	 * @return string
	 */
	public static function xrefDeleteUrl($mnfrID, $mnfritemID, $itemID) {
		$url = new Purl(self::xrefUrl($mnfrID, $mnfritemID, $itemID));
		$url->query->set('action', 'delete-xref');
		return $url->getUrl();
	}

/* =============================================================
	Hook Functions
============================================================= */
	public static function initHooks() {
		$m = BaseMxrfe::mxrfeMaster();

		$m->addHook('Page(pw_template=itm)::xrefUrl', function($event) {
			$p = $event->object;
			$mnfrID     = $event->arguments(0);
			$mnfritemID = $event->arguments(1);
			$itemID       = $event->arguments(2);
			$event->return = self::xrefUrl($mnfrID, $mnfritemID, $itemID);
		});

		$m->addHook('Page(pw_template=itm)::xrefDeleteUrl', function($event) {
			$p = $event->object;
			$mnfrID     = $event->arguments(0);
			$mnfritemID = $event->arguments(1);
			$itemID       = $event->arguments(2);
			$event->return = self::xrefDeleteUrl($mnfrID, $mnfritemID, $itemID);
		});

		$m->addHook('Page(pw_template=itm)::xrefExitUrl', function($event) {
			$p = $event->object;
			$xref = $event->arguments(0); // Xref
			$event->return = Xrefs::xrefUrlMxrfe($xref->itemid);
		});
	}
}
