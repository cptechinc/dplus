<?php namespace Controllers\Mgl;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Mvc Controllers
use Controllers\Mgl\Base;

class Menu extends Base {
	const DPLUSPERMISSION = 'mgl';
	const SUBFUNCTIONS = [
		'glmain' => [
			'name'       => 'glmain',
			'permission' => 'glmain',
			'title'      => 'Maintenance',
			'summary'    => 'General Ledger Maintenance'
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
		self::initHooks();
		return self::menu($data);
	}


/* =============================================================
	URLs
============================================================= */
	public static function mglUrl() {
		return self::pw('pages')->get('pw_template=mgl')->url;
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::mglUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function glmainUrl() {
		return self::subfunctionUrl('glmain');
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

		$m->addHook('Page(pw_template=mgl)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mgl)::mglUrl', function($event) {
			$event->return = self::mglUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mgl)::glmainUrl', function($event) {
			$event->return = self::glmainUrl($event->arguments(0));
		});
	}
}
