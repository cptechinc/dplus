<?php namespace Controllers\Mpr;
// Purl Library
use Purl\Url as Purl;
// Controllers
use Controllers\Templates\AbstractMenuController;

/**
 * Mpr\Menu
 * 
 * Class for rendering the Mpr Menu
 */
class Menu extends AbstractMenuController {
	const DPLUSPERMISSION = 'mpr';
	const TITLE = 'Prospecting';
	const SUBFUNCTIONS = [
		'prman' => [
			'name'       => 'prman',
			'permission' => 'prman',
			'title'      => 'Maintenance',
			'summary'    => 'Prospect Maintenance'
		]
	];

/* =============================================================
	URLs
============================================================= */
	public static function _url() {
		return self::pw('pages')->get('pw_template=mpr')->url;
	}

	public static function mprUrl() {
		return self::url();
	}

	public static function prmanUrl() {
		return self::subfunctionUrl('prman');
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
