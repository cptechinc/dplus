<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemXrefVendorQuery, ItemXrefVendor;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Filters
use Dplus\Filters;

class Substitutes extends Base {
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

		$filter = new Filters\Min\ItemSubstitute();
		$filter->itemid($data->itemID);
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 10);
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
	private static function displaySubstitutes($data, PropelModelPager $xrefs) {

	}
	
/* =============================================================
	Hook Functions
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMin');
	}
}
