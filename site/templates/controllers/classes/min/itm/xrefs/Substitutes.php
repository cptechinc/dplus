<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
use ItemSubstituteQuery, ItemSubstitute;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Min\Inmain\Itm\Substitutes as CRUDManager;

class Substitutes extends Base {
	private static $crud;

	public static function index($data) {
		$fields = ['itemID|text', 'subitemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->subitemID) == false) {
			return self::sub($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParameters($data, $fields);
	}

	private static function list($data) {
		self::pw('page')->headline = "ITM: $data->itemID Substitutes";

		$filter = new Filters\Min\ItemSubstitute();
		$filter->itemid($data->itemID);
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 10);
		return self::displayList($data, $xrefs);
	}

	private static function sub($data) {
		self::pw('page')->headline = "ITM: $data->itemID Substitute $data->subitemID";

		$itmSub = self::getItmSubstitutes();
		$item   = $itmSub->getItm()->item($data->itemID);
		$sub    = $itmSub->getOrCreateSubstitute($data->itemID, $data->subitemID);

		return self::displaySub($data, $item, $sub);
	}


/* =============================================================
	Url Functions
============================================================= */
	public static function subUrl($itemID, $subitemID) {
		$url = new Purl(self::xrefUrlSubstitutes($itemID));
		$url->query->set('subitemID', $subitemID);
		return $url->getUrl();
	}

	public static function subDeleteUrl($itemID, $subitemID) {
		$url = new Purl(self::subUrl($itemID, $subitemID));
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayList($data, PropelModelPager $xrefs) {
		self::initHooks();
		$itm  = self::pw('modules')->get('Itm');
		$item = $itm->item($data->itemID);
		$html  = self::breadCrumbs();
		$html .= self::displaySubstitutes($data, $item, $xrefs);
		return $html;
	}

	private static function displaySubstitutes($data, ItemMasterItem $item, PropelModelPager $xrefs) {
		$itmSub = self::getItmSubstitutes();
		return self::pw('config')->twig->render('items/itm/xrefs/substitutes/list/display.twig', ['item' => $item, 'substitutes' => $xrefs, 'itmSub' => $itmSub]);
	}

	private static function displaySub($data, ItemMasterItem $item, ItemSubstitute $sub) {
		self::initHooks();
		$itmSub = self::getItmSubstitutes();

		$html   = self::breadCrumbs();
		$html  .= self::pw('config')->twig->render('items/itm/xrefs/substitutes/sub/display.twig', ['item' => $item, 'sub' => $sub, 'itmSub' => $itmSub]);
		return $html;
	}

/* =============================================================
	Hook Functions
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMin');

		$m->addHook('Page(pw_template=itm)::subListUrl', function($event) {
			$event->return = self::xrefUrlSubstitutes($event->arguments(0));
		});

		$m->addHook('Page(pw_template=itm)::subUrl', function($event) {
			$event->return = self::subUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=itm)::subNewUrl', function($event) {
			$event->return = self::subUrl($event->arguments(0), 'new');
		});

		$m->addHook('Page(pw_template=itm)::subDeleteUrl', function($event) {
			$event->return = self::subDeleteUrl($event->arguments(0), $event->arguments(1));
		});
	}

/* =============================================================
	Supplmental Functions
============================================================= */
	public static function getItmSubstitutes() {
		if (empty(self::$crud)) {
			$crud = new CRUDManager();
			$crud->init();
			self::$crud = $crud;
		}
		return self::$crud;
	}
}
