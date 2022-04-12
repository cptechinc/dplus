<?php namespace Controllers\Wm\Inventory\Provalley;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Mvc Controllers
use Controllers\Mwm\Base;

class Inventory extends Base {
	const DPLUSPERMISSION = 'wm';
	const SUBFUNCTIONS = [
		'print-gs1' => [
			'name'        => 'Print GS1',
			'description' => 'Print GS1 Labels'
		]
	];

/* =============================================================
	Indexes
============================================================= */
	static public function index($data) {
		$fields = ['scan|text'];
		self::sanitizeParametersShort($data, $fields);
		return self::menu($data);
	}


/* =============================================================
	Data Processing
============================================================= */


/* =============================================================
	URLs
============================================================= */
	static public function subfunctionUrl($key) {
		$url = new Purl(self::pw('pages')->get('pw_template=whse-inv-provalley')->url);
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	static private function menu($data) {
		return self::pw('config')->twig->render('warehouse/inventory/provalley/menu.twig', ['functions' => self::SUBFUNCTIONS]);
	}

/* =============================================================
	Requests
============================================================= */

/* =============================================================
	Validator, Module Getters
============================================================= */
	static public function validateUserPermission(User $user = null) {
		if (empty($user)) {
			$user = self::pw('user');
		}
		return $user->has_function(self::DPLUSPERMISSION);
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('WarehouseManagement');

		$m->addHook('Page(pw_template=whse-inv-provalley)::subfunctionUrl', function($event) {
			$event->return = self::SubfunctionUrl($event->arguments(0));
		});

	}
}
