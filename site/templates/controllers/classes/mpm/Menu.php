<?php namespace Controllers\Mpm;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Mvc Controllers
use Controllers\Min\Inproc\Base;

class Menu extends Base {
	const DPLUSPERMISSION = '';
	const SUBFUNCTIONS = [
		'pmmain' => [
			'name'       => 'pmmain',
			'permission' => '', // TOOD
			'title'      => 'Maintenance',
			'summary'    => ' Production Management Maintenance'
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
		$url = new Purl(self::pw('pages')->get('pw_template=mpm')->url);
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
			if (empty($function['permission']) || self::pw('user')->hasPermission($function['permission'])) {
				$functions[$key] = $function;
			}
		}
		return self::pw('config')->twig->render('min/inproc/menu.twig', ['functions' => $functions]);
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mpm)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});
	}
}
