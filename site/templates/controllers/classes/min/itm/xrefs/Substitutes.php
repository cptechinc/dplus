<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Min\Inmain\Itm\Substitutes as CRUDManager;

class Substitutes extends Base {
	private static $crud;

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

		if (empty($data->vendoritemID) == false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	private static function list($data) {
		self::pw('page')->headline = "ITM: $data->itemID Substitutes";

		$itm = self::pw('modules')->get('Itm');
		$item = $itm->item($data->itemID);
		$filter = new Filters\Min\ItemSubstitute();
		$filter->itemid($data->itemID);
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 10);
		return self::displaySubstitutes($data, $item, $xrefs);
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParameters($data, $fields);
	}


/* =============================================================
	Url Functions
============================================================= */

/* =============================================================
	Display Functions
============================================================= */
	private static function displaySubstitutes($data, ItemMasterItem $item, PropelModelPager $xrefs) {
		$itmSub = self::getItmSubstitutes();
		return self::pw('config')->twig->render('items/itm/xrefs/substitutes/list/display.twig', ['item' => $item, 'substitutes' => $xrefs, 'itmSub' => $itmSub]);
	}

/* =============================================================
	Hook Functions
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMin');
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
