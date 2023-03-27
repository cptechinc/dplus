<?php namespace Controllers\Min\Inmain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Controllers
use Controllers\Templates\AbstractMenuController;

/**
 * Inmain\Menu
 * 
 * Class for rendering the Inmain Menu
 */
class Menu extends AbstractMenuController {
	const DPLUSPERMISSION = 'inmain';
	const TITLE = 'Maintenance';
	const PARENT_MENU_CODE = 'min';
	const SUBFUNCTIONS = [
		'addm' => [
			'name'       => 'addm',
			'permission' => 'addm',
			'title'      => 'Add-On Item Maintenance',
			'summary'    => 'View / Edit Add-On Items'
		],
		'csccm' => [
			'name'       => 'csccm',
			'permission' => Csccm::DPLUSPERMISSION,
			'title'      => Csccm::TITLE,
			'summary'    => Csccm::SUMMARY
		],
		'i2i' => [
			'name'       => 'i2i',
			'permission' => 'i2i',
			'title'      => 'Item To Item',
			'summary'    => 'View / Edit Item To Item X-Ref'
		],
		'iarn' => [
			'name'       => 'iarn',
			'permission' => Iarn::DPLUSPERMISSION,
			'title'      => Iarn::TITLE,
			'summary'    => Iarn::SUMMARY
		],
		'iasm' => [
			'name'       => 'iasm',
			'permission' => Iasm::DPLUSPERMISSION,
			'title'      => Iasm::TITLE,
			'summary'    => Iasm::SUMMARY
		],
		'igcm' => [
			'name'       => 'igcm',
			'permission' => Igcm::DPLUSPERMISSION,
			'title'      => Igcm::TITLE,
			'summary'    => Igcm::SUMMARY
		],
		'igm' => [
			'name'       => 'igm',
			'permission' => Igm::DPLUSPERMISSION,
			'title'      => Igm::TITLE,
			'summary'    => Igm::SUMMARY
		],
		'igpm' => [
			'name'       => 'igpm',
			'permission' => Igpm::DPLUSPERMISSION,
			'title'      => Igpm::TITLE,
			'summary'    => Igpm::SUMMARY
		],
		'iplm' => [
			'name'       => 'iplm',
			'permission' => Iplm::DPLUSPERMISSION,
			'title'      => Iplm::TITLE,
			'summary'    => Iplm::SUMMARY
		],
		'ioptm' => [
			'name'       => 'ioptm',
			'permission' => 'ioptm',
			'title'      => 'Inventory Optional Codes',
			'summary'    => 'View / Edit Inventory Optional Code'
		],
		'itm' => [
			'name'       => 'itm',
			'permission' => 'itm',
			'title'      => 'Item Maintenance',
			'summary'    => 'View / Edit Item'
		],
		'itmimg' => [
			'name'       => 'itmimg',
			'permission' => 'itm',
			'title'      => 'Item Images',
			'summary'    => 'View / Edit Item Images'
		],
		'itmp' => [
			'name'       => 'itmp',
			'permission' => 'itmp',
			'title'      => 'Item Maintenance Permissions',
			'summary'    => 'View / Edit ITM Permissions'
		],
		'iwhm' => [
			'name'       => 'iwhm',
			'permission' => Iwhm::DPLUSPERMISSION,
			'title'      => Iwhm::TITLE,
			'summary'    => Iwhm::SUMMARY
		],
		'msdsm' => [
			'name'       => 'msdsm',
			'permission' => Msdsm::DPLUSPERMISSION,
			'title'      => Msdsm::TITLE,
			'summary'    => Msdsm::SUMMARY
		],
		'spit' => [
			'name'       => 'spit',
			'permission' => Spit::DPLUSPERMISSION,
			'title'      => Spit::TITLE,
			'summary'    => Spit::SUMMARY
		],
		'stcm' => [
			'name'       => 'stcm',
			'permission' => Stcm::DPLUSPERMISSION,
			'title'      => Stcm::TITLE,
			'summary'    => Stcm::SUMMARY
		],
		'tarm' => [
			'name'       => 'tarm',
			'permission' => Tarm::DPLUSPERMISSION,
			'title'      => Tarm::TITLE,
			'summary'    => Tarm::SUMMARY
		],
		'umm' => [
			'name'       => 'umm',
			'permission' => Umm::DPLUSPERMISSION,
			'title'      => Umm::TITLE,
			'summary'    => Umm::SUMMARY
		],
		'upcx' => [
			'name'       => 'upcx',
			'permission' => 'upcx',
			'title'      => 'UPC Item X-Ref',
			'summary'    => 'View / Edit UPC Item X-Refs'
		],
	];

/* =============================================================
	URLs
============================================================= */
	public static function _url() {
		return self::pw('pages')->get('pw_template=inmain')->url;
	}

	public static function addmUrl() {
		return self::subfunctionUrl('addm');
	}

	public static function csccmUrl() {
		return self::subfunctionUrl('csccm');
	}

	public static function i2iUrl() {
		return self::subfunctionUrl('i2i');
	}

	public static function iarnUrl() {
		return self::subfunctionUrl('iarn');
	}

	public static function iasmUrl() {
		return self::subfunctionUrl('iasm');
	}

	public static function igcmUrl() {
		return self::subfunctionUrl('igcm');
	}

	public static function igmUrl() {
		return self::subfunctionUrl('igm');
	}

	public static function igpmUrl() {
		return self::subfunctionUrl('igpm');
	}

	public static function iplmUrl() {
		return self::subfunctionUrl('iplm');
	}

	public static function ioptmUrl() {
		return self::subfunctionUrl('ioptm');
	}

	public static function itmUrl() {
		return self::subfunctionUrl('itm');
	}

	public static function itmimgUrl() {
		return self::subfunctionUrl('itmimg');
	}

	public static function itmpUrl() {
		return self::subfunctionUrl('itmp');
	}

	public static function iwhmUrl() {
		return self::subfunctionUrl('iwhm');
	}

	public static function msdsmUrl() {
		return self::subfunctionUrl('msdsm');
	}

	public static function spitUrl() {
		return self::subfunctionUrl('spit');
	}

	public static function stcmUrl() {
		return self::subfunctionUrl('stcm');
	}

	public static function tarmUrl() {
		return self::subfunctionUrl('tarm');
	}

	public static function ummUrl() {
		return self::subfunctionUrl('umm');
	}

	public static function upcxUrl() {
		return self::subfunctionUrl('upcx');
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMin');

		$m->addHook('Page(pw_template=inmain)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});
	}
}
