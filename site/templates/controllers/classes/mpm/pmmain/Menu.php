<?php namespace Controllers\Mpm\Pmmain;
// Purl Library
use Purl\Url as Purl;
// Controllers
use Controllers\Mpm\Menu as MenuMpm;
// Controllers
use Controllers\Templates\AbstractMenuController;

/**
 * Pmmain\Menu
 * 
 * Class for rendering the Pmmain Menu
 */
class Menu extends AbstractMenuController {
	const DPLUSPERMISSION = 'pmmain';
	const TITLE = 'Maintenance';
	const PARENT_MENU_CODE = 'mpm';
	const SUBFUNCTIONS = [
		'bmm' => [
			'name'       => 'bmm',
			'permission' => 'bmm',
			'title'      => 'Bill-of-Material Maintenance',
			'summary'    => 'View / Edit Bill-of-Materials'
		],
		'dcm' => [
			'name'       => 'dcm',
			'permission' => Dcm::DPLUSPERMISSION,
			'title'      => Dcm::TITLE,
			'summary'    => Dcm::SUMMARY
		],
		'rcm' => [
			'name'       => 'rcm',
			'permission' => Rcm::DPLUSPERMISSION,
			'title'      => Rcm::TITLE,
			'summary'    => Rcm::SUMMARY
		],
	];

/* =============================================================
	URLs
============================================================= */
	public static function _url() {
		return MenuMpm::pmmainUrl();
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
