<?php namespace Controllers\Wm\Inventory;
// Purl Library
use Purl\Url as Purl;
// Dplus Models
use Warehouse;
// ProcessWire Classes, Modules
use ProcessWire\WireData;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\FindItem as Inventory;

// Mvc Controllers
use Controllers\Wm\Base;

class FindItem extends Base {
	const DPLUSPERMISSION = 'pfini';

/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		if (static::validateUserPermission() === false) {
			return static::renderUserNotPermittedAlert();
		}
		self::pw('page')->headline = "Find Item";

		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/warehouse/find-item.js'));
		
		if ($data->q === '') {
			return self::searchForm($data);
		}
		return self::search($data);
	}

	private static function searchForm(WireData $data) {
		return self::pw('config')->twig->render('warehouse/inventory/find-item/search/display.twig');
	}

	private static function search(WireData $data) {
		self::pw('page')->headline = "Find Item: '$data->q'";
		/** @var Warehouse */
		$warehouse = self::getCurrentUserWarehouse();

		$inventory = Inventory::instance();
		$inventory->setWhseid(self::pw('user')->whseid);
		$inventory->requestSearch($data->q);

		$items = $inventory->distinctItemsFromInvsearch();
		return self::pw('config')->twig->render('warehouse/inventory/find-item/results/display.twig', ['warehouse' => $warehouse, 'items' => $items, 'inventory' => $inventory]);
	}

/* =============================================================
	URL functions
============================================================= */
	public static function url($itemID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=whse-bin-inquiry')->url);
		if (empty($itemID)) {
			return $url->getUrl();
		}
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}

	public static function printableUrl($itemID) {
		$url = new Purl(self::url($itemID));
		$url->path->add('print');
		return $url->getUrl();
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('WarehouseManagement');

		$m->addHook('Page(pw_template=whse-find-item)::printableUrl', function($event) {
			$event->return = self::printableUrl($event->arguments(0));
		});
	}
}