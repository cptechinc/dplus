<?php namespace Controllers\Min\Itm\Xrefs;
use Purl\Url as Purl;
// Dplus Model
use ItemXrefManufacturerQuery, ItemXrefManufacturer;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefMxrfe as MxrfeCRUD;
// Dplus Filters
use Dplus\Filters\Map\Mxrfe as MxrfeFilter;
// Mvc Controllers
use Controllers\Min\Itm\Xrefs;
use Controllers\Min\Itm\Xrefs\XrefFunction;
use Controllers\Map\Mxrfe as BaseMxrfe;

class Mxrfe extends XrefFunction {

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

		if (empty($data->mnfritemID) == false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$page    = self::pw('page');
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'mnfrID|text', 'mnfritemID|text', 'action|text'];
		$data  = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');
		$mxrfe = BaseMxrfe::mxrfeMaster();

		if ($data->action) {
			$mxrfe->process_input($input);
		}
		$session = self::pw('session');
		$page    = self::pw('page');
		$session->redirect($page->redirectURL($input), $http301 = false);
	}

	public static function xref($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		self::initHooks();
		$fields = ['itemID|text', 'mnfrID|text', 'mnfritemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$config  = self::pw('config');
		$page    = self::pw('page');
		$pages   = self::pw('pages');
		$itm     = self::getItm();
		$modules = self::pw('modules');
		$qnotes  = $modules->get('QnotesItemMxrfe');
		$mxrfe  = BaseMxrfe::mxrfeMaster();
		$xref   = $mxrfe->get_create_xref($data->mnfrID, $data->mnfritemID, $data->itemID);
		$item   = $itm->get_item($data->itemID);

		$page->headline = "ITM: $item->itemid MXRFE $xref->mnfrid-$xref->mnfritemid";

		if ($xref->isNew()) {
			$xref->setItemid($data->itemID);
			$page->headline = "ITM: $item->itemid MXRFE Create X-ref";
		}

		$html = '';
		$html .= self::mxrfeHeaders();
		$html .= BaseMxrfe::lockXref($xref);
		$html .= $config->twig->render('items/itm/xrefs/mxrfe/form/display.twig', ['xref' => $xref, 'item' => $item, 'mxrfe' => $mxrfe, 'qnotes' => $qnotes, 'customer' => $mxrfe->vendor($data->mnfrID)]);

		if (!$xref->isNew()) {
			$html .= BaseMxrfe::qnotesDisplay($xref);
		}

		$page->js .= $config->twig->render('items/mxrfe/item/form/js.twig', ['mxrfe' => $mxrfe]);
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

	public static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		self::initHooks();
		$fields = ['itemID|text', 'q|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page    = self::pw('page');
		$config  = self::pw('config');
		$modules = self::pw('modules');
		$itm     = self::getItm();
		$item = $itm->get_item($data->itemID);
		$mxrfe = BaseMxrfe::mxrfeMaster();
		$mxrfe->recordlocker->remove_lock();
		$filter = new MxrfeFilter();
		$filter->itemid($data->itemID);
		$filter->sortby($page);
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 10);
		$page->title = "MXRFE";
		$page->headline = "ITM: $data->itemID MXRFE";

		$html = '';
		$html .= self::mxrfeHeaders();
		$html .= $config->twig->render('items/itm/xrefs/mxrfe/list/display.twig', ['item' => $item, 'xrefs' => $xrefs, 'mxrfe' => $mxrfe]);
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
