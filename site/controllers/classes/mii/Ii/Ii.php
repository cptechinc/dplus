<?php namespace Controllers\Mii;
// Purl/Url Library
use Purl\Url as Purl;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
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

	public static function init() {
		$m = self::pw('modules')->get('DpagesMii');
		$m->addHook('Page(pw_template=ii-item)::subfunctions2', function($event) {
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
		$m->addHook('Page(pw_template=ii-item)::subfunctionTitle', function($event) {
			$title = $event->arguments(0);
			if (array_key_exists($event->arguments(0), self::SUBFUNCTIONS)) {
				$title = self::SUBFUNCTIONS[$event->arguments(0)];
			}
			$event->return = $title;
		});
	}
}
