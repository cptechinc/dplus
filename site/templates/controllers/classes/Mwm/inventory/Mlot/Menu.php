<?php namespace Controllers\Mwm\Inventory\Mlot;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Mvc Controllers
use Controllers\Mwm\Menu as WmMenu;

class Menu extends WmMenu {
	const DPLUSPERMISSION = 'wm';
	const TITLE = 'Warehouse Lots';
	const SUBFUNCTIONS = [
		'img' => [
			'name'       => 'img',
			'permission' => '',
			'title'      => 'Lot Images',
			'summary'    => 'View / Edit Lot Images'
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
	Displays
============================================================= */
	private static function menu($data) {
		$functions = [];
		foreach (self::SUBFUNCTIONS as $key => $function) {
			if (empty($function['permission']) || self::pw('user')->hasPermissionCode($function['permission'])) {
				$functions[$key] = $function;
			}
		}
		self::initHooks();
		return self::displayMenu($data, $functions);
	}

	private static function displayMenu($data, array $functions) {
		$html = '';
		$html .= self::pw('config')->twig->render('dplus-menu/bread-crumbs.twig');
		$html .= self::pw('config')->twig->render('dplus-menu/function-menu.twig', ['functions' => $functions]);
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function menuUrl() {
		return self::pw('pages')->get('pw_template=whse-mlot')->url;
	}

	public static function imgUrl() {
		return self::subfunctionUrl('img');
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::menuUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=whse-mlot)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=whse-mlot)::menuUrl', function($event) {
			$event->return = self::menuUrl();
		});
	}
}
