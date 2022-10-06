<?php namespace Controllers\Mpm;
// Controllers
use Controllers\Templates\AbstractMenuController;

/**
 * Mpm\Menu
 * 
 * Class for rendering the Mpm Menu
 */
class Menu extends AbstractMenuController {
	const DPLUSPERMISSION = 'mpm';
	const TITLE = 'Production Management';
	const SUBFUNCTIONS = [
		'pmmain' => [
			'name'       => 'pmmain',
			'permission' => 'pmmain',
			'title'      => 'Maintenance',
			'summary'    => ' Production Management Maintenance'
		]
	];

/* =============================================================
	URLs
============================================================= */
	public static function _url() {
		return self::pw('pages')->get('pw_template=mpm')->url;
	}

	public static function mpmUrl() {
		return self::url();
	}

	public static function pmmainUrl() {
		return self::subfunctionUrl('pmmain');
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mpm)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpm)::mpmUrl', function($event) {
			$event->return = self::mpmUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpm)::pmmainUrl', function($event) {
			$event->return = self::pmmainUrl($event->arguments(0));
		});
	}
}
