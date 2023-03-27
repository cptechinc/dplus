<?php namespace Controllers\Map\Apmain;
// Purl Library
use Purl\Url as Purl;
// Controllers
use Controllers\Templates\AbstractMenuController;

/**
 * Apmain\Menu
 * 
 * Class for rendering the Apmain Menu
 */
class Menu extends AbstractMenuController {
	const DPLUSPERMISSION = 'apmain';
	const TITLE = 'Maintenance';
	const PARENT_MENU_CODE = 'map';
	const SUBFUNCTIONS = [
		'aoptm' => [
			'name'       => 'aoptm',
			'permission' => 'aoptm',
			'title'      => 'Accounts Payable Optional Code',
			'summary'    => 'View / Edit Accounts Payable Optional Code'
		],
		'bum' => [
			'name'       => 'bum',
			'permission' => Bum::DPLUSPERMISSION,
			'title'      => Bum::TITLE,
			'summary'    => Bum::SUMMARY
		],
		'cocom' => [
			'name'       => 'cocom',
			'permission' => Cocom::DPLUSPERMISSION,
			'title'      => Cocom::TITLE,
			'summary'    => Cocom::SUMMARY
		],
		'mxrfe' => [
			'name'       => 'mxrfe',
			'permission' => 'mxrfe',
			'title'      => 'Mnfr / Competitor Item X-Ref',
			'summary'    => 'View / Edit Manufacturer / Competitor X-Ref'
		],
		'ptm' => [
			'name'		 => 'ptm',
			'permission' => Ptm::DPLUSPERMISSION,
			'title' 	 => Ptm::TITLE,
			'summary'	 => Ptm::SUMMARY
		],
		'vtm' => [
			'name'		 => 'vtm',
			'permission' => Vtm::DPLUSPERMISSION,
			'title' 	 => Vtm::TITLE,
			'summary'	 => Vtm::SUMMARY
		],
		'vxm' => [
			'name'       => 'vxm',
			'permission' => 'vxm',
			'title'      => 'Vendor Item X-Ref',
			'summary'    => 'View / Edit Vendor Item X-Ref'
		],
	];

/* =============================================================
	URLs
============================================================= */
	public static function mapUrl() {
		return self::pw('pages')->get('dplus_function=map')->url;
	}

	public static function _url() {
		$url = new Purl(self::mapUrl());
		$url->path->add('apmain');
		return $url->getUrl();
	}

	public static function aoptmUrl() {
		return self::subfunctionUrl('aoptm');
	}

	public static function bumUrl() {
		return self::subfunctionUrl('bum');
	}

	public static function cocomUrl() {
		return self::subfunctionUrl('cocom');
	}

	public static function mxrfeUrl() {
		return self::subfunctionUrl('mxrfe');
	}

	public static function ptmUrl() {
		return self::subfunctionUrl('ptm');
	}

	public static function vtmUrl() {
		return self::subfunctionUrl('vtm');
	}

	public static function vxmUrl() {
		return self::subfunctionUrl('vxm');
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=apmain)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=apmain)::apmainUrl', function($event) {
			$event->return = self::url();
		});
	}
}
