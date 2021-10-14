<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemAddonItemQuery, ItemAddonItem;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Filters
use Dplus\Filters;
// Mvc Controllers
use Controllers\Min\Inmain\Addm as AddmParent;

class Addm extends Base {
	const PERMISSION_ITMP = 'xrefs';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'addonID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		// if (empty($data->action) === false) {
		// 	return self::handleCRUD($data);
		// }

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->component) === false) {
			return self::bomComponent($data);
		}
		return self::list($data);
	}

	// public static function handleCRUD($data) {
	// 	if (self::validateItemidAndPermission($data) === false) {
	// 		return self::displayAlertUserPermission($data);
	// 	}
	// 	self::sanitizeParametersShort($data, ['bomID|text', 'itemID|text', 'action|text']);
	//
	// 	if (empty($data->bomID)) {
	// 		self::setupInputBomid($data);
	// 	}
	// 	$addm = AddmParent::getAddm();
	//
	// 	if ($data->action) {
	// 		$addm->processInput(self::pw('input'));
	// 	}
	// 	self::pw('session')->redirect(self::bomUrl($data->itemID), $http301 = false);
	// }

	private static function list($data) {
		self::pw('page')->headline = "ITM: $data->itemID Add-ons";

		$filter = new Filters\Min\AddonItem();
		$filter->query->filterByItemid($data->itemID);
		$filter->sortby(self::pw('page'));
		$filter->query->orderBy(ItemAddonItem::aliasproperty('itemid'), 'ASC');
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 10);
		$html = self::displayList($data, $xrefs);
		return $html;
	}

	// private static function bomComponent($data) {
	// 	$fields = ['itemID|text', 'component|text', 'action|text'];
	// 	self::sanitizeParametersShort($data, $fields);
	//
	// 	$addm  = AddmParent::getAddm();
	// 	$addonID = $addm->components->getOrCreate($data->itemID, $data->component);
	//
	// 	if ($addonID->isNew() === false) {
	// 		$addm->lockrecord($data->itemID);
	// 	}
	//
	// 	$page           = self::pw('page');
	// 	$page->headline = $addonID->isNew() ? "ITM: BoM $data->itemID" : "ITM: BoM $data->itemID - $data->component";
	// 	$page->js .= self::pw('config')->twig->render('mpm/bmm/component/js.twig', ['bmm' => $addm]);
	// 	$html = self::displayBomComponent($data, $addonID);
	// 	return $html;
	// }

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $xrefs) {
		$addm  = AddmParent::getAddm();
		$itm  = self::getItm();
		$item = $itm->item($data->itemID);
		self::initHooks();

		$html = '';
		$html .= self::lockItem($data->itemID);
		$html .= AddmParent::displayResponse($data);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/addm/list/display.twig', ['itm' => $itm, 'addm' => $addm, 'item' => $item, 'xrefs' => $xrefs]);
		$addm->deleteResponse();
		return $html;
	}

	// private static function displayBomComponent($data, $addonID) {
	// 	$data->bomID = $data->itemID;
	// 	$addm      = AddmParent::getAddm();
	// 	$itm      = self::getItm();
	// 	$item     = $itm->item($data->itemID);
	// 	$bomItem  = $addm->header->getOrCreate($data->itemID);
	// 	self::initHooks();
	// 	AddmParent::lock($data->itemID);
	//
	// 	$html  = '';
	// 	// $html .= self::kitHeaders();
	// 	$html .= self::lockItem($data->itemID);
	// 	$html .= AddmParent::displayLock($data);
	// 	$html .= AddmParent::displayResponse($data);
	// 	$html .= self::pw('config')->twig->render('items/itm/xrefs/bom/component/display.twig', ['item' => $item, 'bmm' => $addm, 'itm' => $itm, 'bomItem' => $bomItem, 'component' => $addonID]);
	// 	$addm::deleteResponse();
	// 	return $html;
	// }

/* =============================================================
	URL Functions
============================================================= */
	public static function addmUrl($itemID, $focus = '') {
		$url = new Purl(Xrefs::xrefUrlAddm($itemID));
		if ($focus) {
			$url->query->set('focus', $focus);
		}
		return $url->getUrl();
	}

	public static function xrefUrl($itemID, $addonID) {
		$url = new Purl(self::addmUrl($itemID));
		$url->query->set('addonID', $addonID);
		return $url->getUrl();
	}

	public static function xrefDeleteUrl($itemID, $addonID) {
		$url = new Purl(self::xrefUrl($itemID, $addonID));
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

/* =============================================================
	Hooks Functions
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=itm)::addmUrl', function($event) {
			$itemID = $event->arguments(0);
			$focus = $event->arguments(1);
			$event->return = self::addmUrl($itemID, $focus);
		});

		$m->addHook('Page(pw_template=itm)::addmExitUrl', function($event) {
			$itemID = $event->arguments(0);
			$event->return = Xrefs::xrefsUrl($itemID);
		});

		$m->addHook('Page(pw_template=itm)::xrefUrl', function($event) {
			$itemID = $event->arguments(0);
			$addonID = $event->arguments(1);
			$event->return = self::xrefUrl($itemID, $addonID);
		});

		$m->addHook('Page(pw_template=itm)::xrefNewUrl', function($event) {
			$itemID = $event->arguments(0);
			$event->return = self::xrefUrl($itemID, 'new');
		});

		$m->addHook('Page(pw_template=itm)::xrefDeleteUrl', function($event) {
			$itemID  = $event->arguments(0);
			$addonID = $event->arguments(1);
			$event->return = self::xrefDeleteUrl($itemID, $addonID);
		});

		$m->addHook('Page(pw_template=itm)::xrefExitUrl', function($event) {
			$itemID  = $event->arguments(0);
			$addonID = $event->arguments(1);
			$event->return = self::addmUrl($itemID, $addonID);
		});
	}
}
