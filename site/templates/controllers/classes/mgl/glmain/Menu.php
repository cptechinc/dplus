<?php namespace Controllers\Mgl\Glmain;
// Purl Library
use Purl\Url as Purl;
// Controllers
use Controllers\Mgl\Menu as MenuMgl;

class Menu extends Base {
	const DPLUSPERMISSION = 'glmain';
	const TITLE = 'Maintenance';
	const SUBFUNCTIONS = [
		'ttm' => [
			'name'       => 'ttm',
			'permission' => 'ttm',
			'title'      => 'Statement Text Code',
			'summary'    => 'View / Edit Statement Text Code'
		],
		'dtm' => [
			'name'       => 'dtm',
			'permission' => Dtm::DPLUSPERMISSION,
			'title'      => Dtm::TITLE,
			'summary'    => Dtm::SUMMARY
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

	public static function url() {
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

		$m->addHook('Page(pw_template=mgl)::menuTitle', function($event) {
			$event->return = MenuMgl::TITLE;
		});

		$m->addHook('Page(pw_template=mgl)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mgl)::ttmUrl', function($event) {
			$event->return = self::ttmUrl($event->arguments(0));
		});
	}
}
