<?php namespace Controllers\Wm\Inventory;
// Purl Library
use Purl\Url as Purl;
// Dplus Models
use Warehouse;
use Invsearch;
// ProcessWire Classes, Modules
use ProcessWire\User;
use ProcessWire\WireData;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\BinInquiry as BinInventory;
use Dplus\Wm\Inventory\Search as InventorySearch;
// Mvc Controllers
use Controllers\Wm\Base;

class BinInquiry extends Base {
	const DPLUSPERMISSION = 'pbini';

/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['binID|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->headline = "Bin Inquiry";

		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/warehouse/bin-inquiry.js'));
		
		if ($data->binID === '') {
			return self::selectBin($data);
		}
		return self::bin($data);
	}

	private static function selectBin(WireData $data) {
		/** @var Warehouse */
		$warehouse = self::getCurrentUserWarehouse();

		return self::pw('config')->twig->render('warehouse/inventory/bin-inquiry/select-bins/display.twig', ['warehouse' => $warehouse]);
	}

	private static function bin(WireData $data) {
		self::pw('page')->headline = "Bin Inquiry: $data->binID";
		/** @var Warehouse */
		$warehouse = self::getCurrentUserWarehouse();

		$inventory = BinInventory::instance();
		$inventory->setWhseid(self::pw('user')->whseid);

		$items = $inventory->distinctItems($data->binID);
		return self::pw('config')->twig->render('warehouse/inventory/bin-inquiry/results/display.twig', ['warehouse' => $warehouse, 'items' => $items, 'inventory' => $inventory]);
	}
}