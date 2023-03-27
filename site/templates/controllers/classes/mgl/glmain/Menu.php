<?php namespace Controllers\Mgl\Glmain;
// Purl Library
use Purl\Url as Purl;
// Controllers
use Controllers\Templates\AbstractMenuController;
use Controllers\Mgl\Menu as MglMenu;

/**
 * Glmain\Menu
 * 
 * Class for rendering the Glmain Menu
 */
class Menu extends AbstractMenuController {
	const DPLUSPERMISSION = 'glmain';
	const TITLE = 'Maintenance';
	const PARENT_MENU_CODE = 'mgl';
	const SUBFUNCTIONS = [
		'ttm' => [
			'name'       => 'ttm',
			'permission' => Ttm::DPLUSPERMISSION,
			'title'      => Ttm::TITLE,
			'summary'    => Ttm::SUMMARY
		],
		'dtm' => [
			'name'       => 'dtm',
			'permission' => Dtm::DPLUSPERMISSION,
			'title'      => Dtm::TITLE,
			'summary'    => Dtm::SUMMARY
		]
	];

/* =============================================================
	URLs
============================================================= */
	public static function _url() {
		return MglMenu::glmainUrl();
	}

	public static function dtmUrl() {
		return self::subfunctionUrl('dtm');
	}

	public static function ttmUrl() {
		return self::subfunctionUrl('ttm');
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=mgl)::menuTitle', function($event) {
			$event->return = MglMenu::TITLE;
		});

		$m->addHook('Page(pw_template=mgl)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mgl)::ttmUrl', function($event) {
			$event->return = self::ttmUrl($event->arguments(0));
		});
	}
}
