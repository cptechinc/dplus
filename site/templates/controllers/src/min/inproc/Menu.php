<?php namespace Controllers\Min\Inproc;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Mvc Controllers
use Controllers\Min\Inproc\Base;

class Menu extends Base {
	const DPLUSPERMISSION = 'inproc';
	const SUBFUNCTIONS = [
		'iarn' => [
			'name'       => 'iarn',
			'permission' => 'iarn',
			'title'      => 'Inventory Adjustment Reason',
			'summary'    => 'View / Edit Inventory Adjustment Reason'
		]
	];

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, []);
		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
		}
		return self::menu($data);
	}


/* =============================================================
	URLs
============================================================= */
	public static function subfunctionUrl($key) {
		$url = new Purl(self::pw('pages')->get('pw_template=inproc')->url);
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function menu($data) {
		$functions = [];
		foreach (self::SUBFUNCTIONS as $key => $function) {
			if (empty($function['permission']) || self::pw('user')->hasPermissionCode($function['permission'])) {
				$functions[$key] = $function;
			}
		}
		return self::pw('config')->twig->render('min/inproc/menu.twig', ['functions' => $functions]);
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMin');

		$m->addHook('Page(pw_template=inproc)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});
	}
}
