<?php namespace Controllers\Mpm\Pmmain;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Mvc Controllers
use Controllers\Mpm\Base;

class Menu extends Base {
	const DPLUSPERMISSION = '';
	const SUBFUNCTIONS = [
		'bmm' => [
			'name'       => 'pmadmn',
			'permission' => '', // TOOD
			'title'      => 'Bill-of-Material Maintenance',
			'summary'    => 'View / Edit Bill-of-Materials'
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

/* =============================================================
	Displays
============================================================= */
	private static function menu($data) {
		$functions = [];
		foreach (self::SUBFUNCTIONS as $key => $function) {
			if (empty($function['permission']) || self::pw('user')->hasPermission($function['permission'])) {
				$functions[$key] = $function;
			}
		}
		self::initHooks();
		return self::pw('config')->twig->render('min/inproc/menu.twig', ['functions' => $functions]);
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mpm)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});
	}
}
