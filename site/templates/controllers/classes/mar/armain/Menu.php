<?php namespace Controllers\Mar\Armain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Controllers
use Controllers\Templates\AbstractMenuController;

/**
 * Armain\Menu
 * 
 * Class for rendering the Armain Menu
 */
class Menu extends AbstractMenuController  {
	const DPLUSPERMISSION = 'armain';
	const TITLE = 'Maintenance';
	const SUBFUNCTIONS = [
		'ccm' => [
			'name'       => 'ccm',
			'permission' => Ccm::DPLUSPERMISSION,
			'title'      => Ccm::TITLE,
			'summary'    => Ccm::SUMMARY
		],
		'cocom' => [
			'name'       => 'cocom',
			'permission' => Cocom::DPLUSPERMISSION,
			'title'      => Cocom::TITLE,
			'summary'    => Cocom::SUMMARY
		],
		'cpm' => [
			'name'       => 'cpm',
			'permission' => Cpm::DPLUSPERMISSION,
			'title'      => Cpm::TITLE,
			'summary'    => Cpm::SUMMARY
		],
		'crcd' => [
			'name'       => 'crcd',
			'permission' => Crcd::DPLUSPERMISSION,
			'title'      => Crcd::TITLE,
			'summary'    => Crcd::SUMMARY
		],
		'crtm' => [
			'name'       => 'crtm',
			'permission' => Crtm::DPLUSPERMISSION,
			'title'      => Crtm::TITLE,
			'summary'    => Crtm::SUMMARY
		],
		'cuc' => [
			'name'       => 'cuc',
			'permission' => Cuc::DPLUSPERMISSION,
			'title'      => Cuc::TITLE,
			'summary'    => Cuc::SUMMARY
		],
		'mtm' => [
			'name'       => 'mtm',
			'permission' => Mtm::DPLUSPERMISSION,
			'title'      => Mtm::TITLE,
			'summary'    => Mtm::SUMMARY
		],
		'pty3' => [
			'name'       => 'pty3',
			'permission' => Pty3::DPLUSPERMISSION,
			'title'      => Pty3::TITLE,
			'summary'    => Pty3::SUMMARY
		],
		'roptm' => [
			'name'       => 'roptm',
			'permission' => 'roptm',
			'title'      => 'Receivables Optional Code',
			'summary'    => 'View / Edit AR Optional Codes'
		],
		'sic' => [
			'name'       => 'sic',
			'permission' => Sic::DPLUSPERMISSION,
			'title'      => Sic::TITLE,
			'summary'    => Sic::SUMMARY
		],
		'spgpm' => [
			'name'       => 'spgpm',
			'permission' => Spgpm::DPLUSPERMISSION,
			'title'      => Spgpm::TITLE,
			'summary'    => Spgpm::SUMMARY
		],
		'suc' => [
			'name'       => 'suc',
			'permission' => Suc::DPLUSPERMISSION,
			'title'      => Suc::TITLE,
			'summary'    => Suc::SUMMARY
		],
		'tm' => [
			'name'       => 'tm',
			'permission' => Tm::DPLUSPERMISSION,
			'title'      => Tm::TITLE,
			'summary'    => Tm::SUMMARY
		],
		'worm' => [
			'name'       => 'worm',
			'permission' => Worm::DPLUSPERMISSION,
			'title'      => Worm::TITLE,
			'summary'    => Worm::SUMMARY
		],
	];

/* =============================================================
	URLs
============================================================= */
	public static function _url() {
		return self::pw('pages')->get('pw_template=armain')->url;
	}
	
	public static function ccmUrl() {
		return self::subfunctionUrl('ccm');
	}

	public static function cocomUrl() {
		return self::subfunctionUrl('cocom');
	}

	public static function cpmUrl() {
		return self::subfunctionUrl('cpm');
	}

	public static function crcdUrl() {
		return self::subfunctionUrl('crcd');
	}

	public static function crtmUrl() {
		return self::subfunctionUrl('crtm');
	}

	public static function cucUrl() {
		return self::subfunctionUrl('cuc');
	}

	public static function mtmUrl() {
		return self::subfunctionUrl('mtm');
	}

	public static function pty3Url() {
		return self::subfunctionUrl('pty3');
	}

	public static function roptmUrl() {
		return self::subfunctionUrl('roptm');
	}

	public static function sicUrl() {
		return self::subfunctionUrl('sic');
	}

	public static function spgpmUrl() {
		return self::subfunctionUrl('spgpm');
	}

	public static function sucUrl() {
		return self::subfunctionUrl('suc');
	}

	public static function tmUrl() {
		return self::subfunctionUrl('tm');
	}
	
	public static function wormUrl() {
		return self::subfunctionUrl('worm');
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMar');

		$m->addHook('Page(pw_template=armain)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});
	}
}
