<?php namespace Controllers\Min\Inproc;
// Purl Library
use Purl\Url as Purl;
// Controllers
use Controllers\Templates\AbstractMenuController;

/**
 * Inproc\Menu
 * 
 * Class for rendering the Inproc Menu
 */
class Menu extends AbstractMenuController {
	const DPLUSPERMISSION = 'inproc';
	const SUBFUNCTIONS = [
	];

/* =============================================================
	URLs
============================================================= */
	public static function _url() {
		return self::pw('pages')->get('pw_template=inproc')->url;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMin');

		$m->addHook('Page(pw_template=inproc)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});
	}
}
