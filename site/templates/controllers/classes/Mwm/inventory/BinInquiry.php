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
		if (static::validateUserPermission() === false) {
			return static::renderUserNotPermittedAlert();
		}
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

/* =============================================================
	URL functions
============================================================= */
	public static function url($binID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=whse-bin-inquiry')->url);
		if (empty($binID)) {
			return $url->getUrl();
		}
		$url->query->set('binID', $binID);
		return $url->getUrl();
	}

	public static function printableUrl($binID) {
		$url = new Purl(self::url($binID));
		$url->path->add('print');
		return $url->getUrl();
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('WarehouseManagement');

		$m->addHook('Page(pw_template=whse-bin-inquiry)::printableUrl', function($event) {
			$event->return = self::printableUrl($event->arguments(0));
		});
	}
}