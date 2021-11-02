<?php namespace Controllers\Mgl\Glmain;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
use Controllers\Mgl\Menu as MenuMgl;

class Menu extends Base {
	const DPLUSPERMISSION = 'glmain';
	const SUBFUNCTIONS = [
		'ttm' => [
			'name'       => 'ttm',
			'permission' => 'ttm',
			'title'      => 'Statement Text Code',
			'summary'    => 'View / Edit Statement Text Code'
		],
		'dtm' => [
			'name'       => 'dtm',
			'permission' => 'dtm',
			'title'      => 'Distribution Code',
			'summary'    => 'View / Edit Distribution Text Code'
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
		self::pw('page')->headline = "General Ledger Maintenance";
		return self::menu($data);
	}

/* =============================================================
	URLs
============================================================= */
	public static function menuUrl() {
		return MenuMgl::glmainUrl();
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::menuUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function ttmUrl() {
		return self::subfunctionUrl('ttm');
	}

	public static function dtmUrl() {
		return self::subfunctionUrl('dtm');
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
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=mgl)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mgl)::ttmUrl', function($event) {
			$event->return = self::ttmUrl($event->arguments(0));
		});
	}
}
