<?php namespace Controllers\Mpm;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Mvc Controllers
use Controllers\Mpm\Base;

class Menu extends Base {
	const DPLUSPERMISSION = 'mpm';
	const SUBFUNCTIONS = [
		'pmmain' => [
			'name'       => 'pmmain',
			'permission' => 'pmmain',
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
	public static function mpmUrl() {
		return self::pw('pages')->get('pw_template=mpm')->url;
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::mpmUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function pmmainUrl() {
		return self::subfunctionUrl('pmmain');
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
		return self::pw('config')->twig->render('dplus-menu/function-menu.twig', ['functions' => $functions]);
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mpm)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpm)::mpmUrl', function($event) {
			$event->return = self::mpmUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpm)::pmmainUrl', function($event) {
			$event->return = self::pmmainUrl($event->arguments(0));
		});
	}
}
