<?php namespace Controllers\Mar\Armain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Mvc Controllers
use Controllers\Mar\AbstractController as Base;

class Menu extends Base {
	const DPLUSPERMISSION = 'armain';
	const TITLE = 'Maintenance';
	const SUBFUNCTIONS = [
		'ccm' => [
			'name'       => 'ccm',
			'permission' => 'ccm',
			'title'      => 'Customer Commission Code',
			'summary'    => 'View / Edit Customer Commission Codes'
		],
		'crtm' => [
			'name'       => 'crtm',
			'permission' => 'crtm',
			'title'      => 'Customer Route Code',
			'summary'    => 'View / Edit Customer Route Codes'
		],
		'roptm' => [
			'name'       => 'roptm',
			'permission' => 'roptm',
			'title'      => 'Receivables Optional Code',
			'summary'    => 'View / Edit AR Optional Codes'
		],
		'spgpm' => [
			'name'       => 'spgpm',
			'permission' => 'spgpm',
			'title'      => 'Salesperson Group Code',
			'summary'    => 'View / Edit AR Salesperson Group Codes'
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
		return self::menu($data);
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		$url = new Purl(parent::menuUrl());
		$url->path->add('armain');
		return $url->getUrl();
	}
	
	public static function menuUrl() {
		return self::url();
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::menuUrl());
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

	public static function roptmUrl() {
		return self::subfunctionUrl('roptm');
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
		return self::pw('config')->twig->render('dplus-menu/function-menu.twig', ['functions' => $functions]);
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMar');

		$m->addHook('Page(pw_template=armain)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});
	}
}
