<?php namespace Controllers\Mgl;
// Purl Library
use Purl\Url as Purl;
// Controllers
use Controllers\Templates\AbstractMenuController;

/**
 * Mgl\Menu
 * 
 * Class for rendering the MGL Menu
 */
class Menu extends AbstractMenuController {
	const DPLUSPERMISSION = 'mgl';
	const TITLE = 'General Ledger';
	const SUBFUNCTIONS = [
		'glmain' => [
			'name'       => 'glmain',
			'permission' => 'glmain',
			'title'      => 'Maintenance',
			'summary'    => 'General Ledger Maintenance'
		]
	];

/* =============================================================
	URLs
============================================================= */
	public static function _url() {
		return self::pw('pages')->get('pw_template=mgl')->url;
	}

	public static function mglUrl() {
		return self::_url();
	}

	public static function glmainUrl() {
		return self::subfunctionUrl('glmain');
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mgl)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mgl)::mglUrl', function($event) {
			$event->return = self::mglUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mgl)::glmainUrl', function($event) {
			$event->return = self::glmainUrl($event->arguments(0));
		});
	}
}
