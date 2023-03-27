<?php namespace Controllers\Mso\Somain;
// Purl URI Library
use Purl\Url as Purl;
// Controllers
use Controllers\Templates\AbstractMenuController;

/**
 * Mso\Menu
 * 
 * Class for rendering the Mso Menu
 */
class Menu extends AbstractMenuController {
	const DPLUSPERMISSION = 'somain';
	const TITLE = 'Maintenance';
	const PARENT_MENU_CODE = 'mso';
	const SUBFUNCTIONS = [
		'cxm' => [
			'name'       => 'cxm',
			'permission' => 'cxm',
			'title'      => 'Customer Item X-Ref',
			'summary'    => 'View / Edit Customer Item X-Ref'
		],
		'lsm' => [
			'name'       => 'lsm',
			'permission' => Lsm::DPLUSPERMISSION,
			'title'      => Lsm::TITLE,
			'summary'    => Lsm::SUMMARY
		],
		'mfcm' => [
			'name'       => 'mfcm',
			'permission' => Mfcm::DPLUSPERMISSION,
			'title'      => Mfcm::TITLE,
			'summary'    => Mfcm::SUMMARY
		],
		'rgarc' => [
			'name'       => 'rgarc',
			'permission' => Rgarc::DPLUSPERMISSION,
			'title'      => Rgarc::TITLE,
			'summary'    => Rgarc::SUMMARY
		],
		'rgasc' => [
			'name'       => 'rgasc',
			'permission' => Rgasc::DPLUSPERMISSION,
			'title'      => Rgasc::TITLE,
			'summary'    => Rgasc::SUMMARY
		],
		'soptm' => [
			'name'       => 'soptm',
			'permission' => 'soptm',
			'title'      => 'Sales Order Optional Code',
			'summary'    => 'View / Edit Sales Order Optional Code'
		],
	];


/* =============================================================
	URLs
============================================================= */
	public static function msoUrl() {
		return self::pw('pages')->get('dplus_function=mso')->url;
	}

	public static function _url() {
		$url = new Purl(self::msoUrl());
		$url->path->add('somain');
		return $url->getUrl();
	}

	public static function somainUrl() {
		return self::url();
	}

	public static function cxmUrl() {
		return self::subfunctionUrl('cxm');
	}

	public static function lsmUrl() {
		return self::subfunctionUrl('lsm');
	}

	public static function mfcmUrl() {
		return self::subfunctionUrl('mfcm');
	}

	public static function rgarcUrl() {
		return self::subfunctionUrl('rgarc');
	}

	public static function rgascUrl() {
		return self::subfunctionUrl('rgasc');
	}

	public static function soptmUrl() {
		return self::subfunctionUrl('soptm');
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=somain)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=somain)::somainUrl', function($event) {
			$event->return = self::somainUrl($event->arguments(0));
		});
	}
}
