<?php namespace Controllers\Mpr;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Mvc Controllers
use Controllers\Mpr\Base;

class Menu extends Base {
	const DPLUSPERMISSION = 'mpr';
	const SUBFUNCTIONS = [
		'prman' => [
			'name'       => 'prman',
			'permission' => 'prman',
			'title'      => 'Maintenance',
			'summary'    => 'Prospect Maintenance'
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
	public static function mprUrl() {
		return self::pw('pages')->get('pw_template=mpr')->url;
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::mprUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function prmanUrl() {
		return self::subfunctionUrl('prman');
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
		return self::displayMenu($data, $functions);
	}

	private static function displayMenu($data, array $functions) {
		$html = '';
		$html .= self::pw('config')->twig->render('dplus-menu/bread-crumbs.twig');
		$html .= self::pw('config')->twig->render('dplus-menu/function-menu.twig', ['functions' => $functions]);
		return $html;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mpr)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpr)::mprUrl', function($event) {
			$event->return = self::mprUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpr)::prmanUrl', function($event) {
			$event->return = self::prmanUrl($event->arguments(0));
		});
	}
}
