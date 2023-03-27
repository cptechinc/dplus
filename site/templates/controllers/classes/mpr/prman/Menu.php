<?php namespace Controllers\Mpr\Prman;
// Purl Library
use Purl\Url as Purl;
// Controllers
use Controllers\Templates\AbstractMenuController;
use Controllers\Mpr\Menu as MenuMpr;

/**
 * Prman\Menu
 * 
 * Class for rendering the Prman Menu
 */
class Menu extends AbstractMenuController {
	const DPLUSPERMISSION = 'prman';
	const TITLE = 'Maintenance';
	const PARENT_MENU_CODE = 'mpr';
	const SUBFUNCTIONS = [
		'src' => [
			'name'       => 'src',
			'permission' => 'src',
			'title'      => 'Source Code',
			'summary'    => 'View / Edit Source Code'
		]
	];


/* =============================================================
	URLs
============================================================= */
	public static function _url() {
		return MenuMpr::prmanUrl();
	}

	public static function srcUrl() {
		return self::subfunctionUrl('src');
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mpr)::menuTitle', function($event) {
			$event->return = MenuMpr::TITLE;
		});

		$m->addHook('Page(pw_template=mpr)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpr)::srcUrl', function($event) {
			$event->return = self::srcUrl($event->arguments(0));
		});
	}
}
