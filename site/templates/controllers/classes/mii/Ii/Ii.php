<?php namespace Controllers\Mii;
// Purl/Url Library
use Purl\Url as Purl;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mii\Ii as Sub;
use Controllers\Mii\Ii\Item;
use Controllers\Mii\Ii\Stock;
use Controllers\Mii\Ii\Requirements;
use Controllers\Mii\Ii\Pricing;
use Controllers\Mii\Ii\Usage;
use Controllers\Mii\Ii\Costing;
use Controllers\Mii\Ii\Activity;
use Controllers\Mii\Ii\Kit;
use Controllers\Mii\Ii\Bom;
use Controllers\Mii\Ii\WhereUsed;
use Controllers\Mii\Ii\Lotserial;
use Controllers\Mii\Ii\General;
use Controllers\Mii\Ii\Substitutes;
use Controllers\Mii\Ii\Documents;
use Controllers\Mii\Ii\SalesOrders;
use Controllers\Mii\Ii\SalesHistory;

class Ii extends AbstractController {
	const SUBFUNCTIONS = [
		'stock'        => 'Stock',
		'requirements' => 'Requirements',
		'costing'      => 'Costing',
		'pricing'      => 'Pricing',
		'usage'        => 'Usage',
		'activity'     => 'Activity',
		'kit'          => 'Kit',
		'bom'          => 'BoM',
		'where-used'   => 'Where Used',
		'lotserial'    => 'Lot / Serial',
		'general'      => 'General',
		'substitutes'  => 'Substitutes',
		'documents'    => 'Documents',
		'sales-orders' => 'Sales Orders',
		'sales-history' => 'Sales History',
		'quotes'        => 'Quotes',
		'purchase-orders' => 'Purchase Orders',
		'purchase-history' => 'Purchase History',
	];

	public static function item($data) {
		return Item::index($data);
	}

	public static function stock($data) {
		return Stock::index($data);
	}

	public static function requirements($data) {
		return Requirements::index($data);
	}

	public static function pricing($data) {
		return Pricing::index($data);
	}

	public static function usage($data) {
		return Usage::index($data);
	}

	public static function costing($data) {
		return Costing::index($data);
	}

	public static function activity($data) {
		return Activity::index($data);
	}

	public static function kit($data) {
		return Kit::index($data);
	}

	public static function bom($data) {
		return Bom::index($data);
	}

	public static function whereUsed($data) {
		return WhereUsed::index($data);
	}

	public static function lotserial($data) {
		return Lotserial::index($data);
	}

	public static function general($data) {
		return General::index($data);
	}

	public static function substitutes($data) {
		return Substitutes::index($data);
	}

	public static function documents($data) {
		return Documents::index($data);
	}

	public static function salesOrders($data) {
		return SalesOrders::index($data);
	}

	public static function salesHistory($data) {
		return SalesHistory::index($data);
	}

	public static function quotes($data) {
		return Sub\Quotes::index($data);
	}

	public static function purchaseOrders($data) {
		return Sub\PurchaseOrders::index($data);
	}

	public static function purchaseHistory($data) {
		return Sub\PurchaseHistory::index($data);
	}

	public static function iiUrl($itemID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		if ($itemID) {
			$url->query->set('itemID', $itemID);
		}
		return $url->getUrl();
	}

	public static function init() {
		Documents::init();

		$m = self::pw('modules')->get('DpagesMii');
		$m->addHook('Page(pw_template=ii-item)::subfunctions', function($event) {
			$user = self::pw('user');
			$allowed = [];
			$iio = Item::getIio();
			foreach (self::SUBFUNCTIONS as $option => $title) {
				if ($iio->allowUser($user, $option)) {
					$allowed[$option] = $title;
				}
			}
			$event->return = $allowed;
		});

		$m->addHook('Page(pw_template=ii-item)::subfunctionURL', function($event) {
			$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
			$url->path->add($event->arguments(1));
			$url->query->set('itemID', $event->arguments(0));
			$event->return = $url->getUrl();
		});

		$m->addHook('Page(pw_template=ii-item)::subfunctionUrl', function($event) {
			$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
			$url->path->add($event->arguments(1));
			$url->query->set('itemID', $event->arguments(0));
			$event->return = $url->getUrl();
		});

		$m->addHook('Page(pw_template=ii-item)::subfunctionTitle', function($event) {
			$title = $event->arguments(0);
			if (array_key_exists($event->arguments(0), self::SUBFUNCTIONS)) {
				$title = self::SUBFUNCTIONS[$event->arguments(0)];
			}
			$event->return = $title;
		});

		$m->addHook('Page(pw_template=ii-item)::itemUrl', function($event) {
			$event->return = self::iiUrl($event->arguments(0));
		});
	}
}
