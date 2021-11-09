<?php namespace Controllers\Mpr\Prman;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
use Controllers\Mpr\Menu as MenuMpr;

class Menu extends Base {
	const DPLUSPERMISSION = 'prman';
	const TITLE_MENU = 'Prospecting';
	const SUBFUNCTIONS = [
		'src' => [
			'name'       => 'src',
			'permission' => 'src',
			'title'      => 'Source Code',
			'summary'    => 'View / Edit Source Code'
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
	public static function menuUrl() {
		return MenuMpr::prmanUrl();
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::menuUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function srcUrl() {
		return self::subfunctionUrl('src');
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
		self::pw('page')->headline = "Prospecting Maintenance";
		return self::pw('config')->twig->render('dplus-menu/function-menu.twig', ['functions' => $functions]);
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mpr)::menuTitle', function($event) {
			$event->return = self::TITLE_MENU;
		});

		$m->addHook('Page(pw_template=mpr)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpr)::srcUrl', function($event) {
			$event->return = self::srcUrl($event->arguments(0));
		});
	}
}
