<?php namespace Controllers\Mar\Armain;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;

class Menu extends Base {
	const DPLUSPERMISSION = 'armain';
	const TITLE = 'Maintenance';
	const SUBFUNCTIONS = [
		'ccm' => [
			'name'       => 'ccm',
			'permission' => 'ccm',
			'title'      => 'Customer Commission Code',
			'summary'    => 'View / Edit Customer Commission Code'
		],
		'crtm' => [
			'name'       => 'crtm',
			'permission' => 'crtm',
			'title'      => 'Customer Route Code',
			'summary'    => 'View / Edit Customer Route Code'
		],
		'spgpm' => [
			'name'       => 'spgpm',
			'permission' => 'spgpm',
			'title'      => 'Salesperson Group Code',
			'summary'    => 'View / Edit Salesperson Group Code'
		],
		'suc' => [
			'name'       => 'suc',
			'permission' => 'suc',
			'title'      => 'Ship-To User Code',
			'summary'    => 'View / Edit Ship-To User Code'
		],
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
	public static function marUrl() {
		return self::pw('pages')->get('dplus_function=mar')->url;
	}

	public static function armainUrl() {
		$url = new Purl(self::marUrl());
		$url->path->add('main');
		return $url->getUrl();
	}

	public static function menuUrl() {
		return self::armainUrl();
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::armainUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function ccmUrl() {
		return self::subfunctionUrl('ccm');
	}

	public static function crtmUrl() {
		return self::subfunctionUrl('crtm');
	}

	public static function spgpmUrl() {
		return self::subfunctionUrl('spgpm');
	}

	public static function sucUrl() {
		return self::subfunctionUrl('suc');
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

		$m->addHook('Page(pw_template=armain)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=armain)::armainUrl', function($event) {
			$event->return = self::armainUrl($event->arguments(0));
		});
	}
}
