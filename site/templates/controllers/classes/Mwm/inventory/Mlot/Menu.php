<?php namespace Controllers\Mwm\Inventory\Mlot;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\User;
// Dplus
use Dplus\Session\UserMenuPermissions;
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
		],
		'labels' => [
			'name'       => 'labels',
			'permission' => '',
			'title'      => 'Labels',
			'summary'    => 'Print Lot Label(s)'
		]
	];

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, []);
		if (static::validateUserPermission() === false) {
			return self::notPermittedDisplay();
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

	public static function labelsUrl() {
		return self::subfunctionUrl('labels');
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

/* =============================================================
	Supplemental
============================================================= */
	public static function validateUserPermission(User $user = null) {
		if (static::validateMenuPermissions($user) === false) {
			return false;
		}
		return parent::validateUserPermission($user);
	}

	public static function validateMenuPermissions(User $user = null) {
		$page   = self::pw('page');

		foreach ($page->parents('template=dplus-menu|warehouse-menu') as $parent) {
			$code = $parent->dplus_function ? $parent->dplus_function : $parent->dplus_permission;

			if (empty($code) === false && UserMenuPermissions::instance()->canAccess($code) === false) {
				return false;
			}
		}
	}
}
