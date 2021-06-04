<?php namespace Controllers\Min\Itm;
// External Libraries, classes
Use Purl\Url as Purl;
// ProcessWire classes, modules
use ProcessWire\Page, ProcessWire\Itm as ItmModel;
// Validators
use Dplus\CodeValidators\Min as MinValidator;
use Dplus\Filters\Min\ItemMaster as ItemMasterFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Min\Itm\Costing;
use Controllers\Min\Itm\Pricing;
use Controllers\Min\Itm\Warehouse;
use Controllers\Min\Itm\Misc;
use Controllers\Min\Itm\Xrefs;

class Itm extends AbstractController {

	const SUBFUNCTIONS = [
		'costing'      => ['title' => 'Costing', 'permission' => 'costing'],
		'pricing'      => ['title' => 'Pricing', 'permission' => 'costing'],
		'warehouses'   => ['title' => 'Warehouses', 'permission' => 'whse'],
		'misc'         => ['title' => 'Misc', 'permission' => 'misc'],
		'xrefs'        => ['title' => 'X-Refs', 'permission' => 'xrefs'],
	];

	public static function item($data) {
		return Item::index($data);
	}

	public static function itemHandleCRUD($data) {
		return Item::handleCRUD($data);
	}

	public static function itemList($data) {
		return Item::list($data);
	}

	public static function costing($data) {
		return Costing::index($data);
	}

	public static function costingHandleCRUD($data) {
		return Costing::handleCRUD($data);
	}

	public static function pricing($data) {
		return Pricing::index($data);
	}

	public static function pricingHandleCRUD($data) {
		return Pricing::handleCRUD($data);
	}

	public static function warehouse($data) {
		return Warehouse::index($data);
	}

	public static function warehouseList($data) {
		return Warehouse::list($data);
	}

	public static function warehouseHandleCRUD($data) {
		return Warehouse::handleCRUD($data);
	}

	public static function misc($data) {
		return Misc::index($data);
	}

	public static function miscHandleCRUD($data) {
		return Misc::handleCRUD($data);
	}

	public static function xrefsHandleCRUD($data) {
		return Xrefs::handleCRUD($data);
	}

	public static function xrefs($data) {
		return Xrefs::index($data);
	}

	public static function itmUrl($itemID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=itm')->url);
		if ($itemID) {
			$url->query->set('itemID', $itemID);
		}
		return $url->getUrl();
	}

	public static function init() {
		$m = self::pw('modules')->get('Itm');

		$m->addHook('Page(pw_template=itm)::subfunctions', function($event) {
			$user = self::pw('user');
			$allowed = [];
			$itmp = ItmFunction::getItmp();

			foreach (self::SUBFUNCTIONS as $option => $function) {
				if ($itmp->allowUser($user, $function['permission'])) {
					$allowed[$option] = $function['title'];
				}
			}
			$event->return = $allowed;
		});
	}
}
