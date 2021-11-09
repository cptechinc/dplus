<?php namespace Controllers\Mpm\Pmmain;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Controllers
use Controllers\Mpm\Menu as MenuMpm;

class Menu extends Base {
	const TITLE = 'Maintenance';

	const SUBFUNCTIONS = [
		'bmm' => [
			'name'       => 'bmm',
			'permission' => 'bmm',
			'title'      => 'Bill-of-Material Maintenance',
			'summary'    => 'View / Edit Bill-of-Materials'
		],
		'dcm' => [
			'name'       => 'dcm',
			'permission' => 'dcm',
			'title'      => 'Work Center Maintenance',
			'summary'    => 'View / Edit Work Centers'
		],
		'rcm' => [
			'name'       => 'rcm',
			'permission' => 'rcm',
			'title'      => 'Resource/Operator Maintenance',
			'summary'    => 'View / Edit Work Resources/Operators'
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
		self::pw('page')->headline = "Production Maintenance";
		return self::menu($data);
	}


/* =============================================================
	URLs
============================================================= */
	public static function menuUrl() {
		$url = new Purl(self::pw('pages')->get('pw_template=mpm')->url);
		$url->path->add('pmmain');
		return $url->getUrl();
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::menuUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function bmmUrl() {
		return self::subfunctionUrl('bmm');
	}

	public static function dcmUrl() {
		return self::subfunctionUrl('dcm');
	}

	public static function rcmUrl() {
		return self::subfunctionUrl('rcm');
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
		self::pw('page')->headline = self::TITLE;
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

		$m->addHook('Page(pw_template=mpm)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpm)::menuTitle', function($event) {
			$event->return = MenuMpm::TITLE;
		});
	}
}
