<?php namespace Controllers\Mpo\Poadmn;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Mvc Controllers
use Controllers\Min\Inproc\Base;

class Menu extends Base {
	const DPLUSPERMISSION = 'poadmn';
	const SUBFUNCTIONS = [
		'cnfm' => [
			'name'       => 'cnfm',
			'permission' => 'cnfm',
			'title'      => 'Confirmation Code',
			'summary'    => 'View / Edit Confirmation Codes'
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
		$url = new Purl(self::pw('pages')->get('pw_template=poadmn')->url);
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
		$m = self::pw('modules')->get('DpagesMpo');

		$m->addHook('Page(pw_template=poadmn)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});
	}
}
