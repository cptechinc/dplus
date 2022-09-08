<?php namespace Controllers\Msa;
// Purl Library
use Purl\Url as Purl;
// Controllers
use Controllers\Templates\AbstractMenuController;

/**
 * Msa\Menu
 * 
 * Class for rendering the Msa Menu
 */
class Menu extends AbstractMenuController {
	const DPLUSPERMISSION = 'msa';
	const TITLE = 'Maintenance';
	const SUBFUNCTIONS = [
		'lgrp' => [
			'name'       => 'lgrp',
			'permission' => 'lgrp',
			'title'      => 'Login Group Entry',
			'summary'    => 'View / Edit Login Groups'
		],
		'noce' => [
			'name'       => 'noce',
			'permission' => 'noce',
			'title'      => 'Pre-Defined Notes Entry',
			'summary'    => 'View / Edit Pre-Defined Notes'
		],
		'logm' => [
			'name'       => 'logm',
			'permission' => 'logm',
			'title'      => 'Login ID Entry',
			'summary'    => 'View / Edit User Logins'
		],
		'sysop' => [
			'name'       => 'sysop',
			'permission' => 'sysop',
			'title'      => 'System Optional Codes',
			'summary'    => 'View / Edit System Optional Codes'
		]
	];

/* =============================================================
	URLs
============================================================= */
	public static function _url() {
		return self::pw('pages')->get('pw_template=msa')->url;
	}

	public static function msaUrl() {
		return self::_url();
	}

	public static function lgrpUrl() {
		return self::subfunctionUrl('lgrp');
	}

	public static function logmUrl() {
		return self::subfunctionUrl('logm');
	}

	public static function noceUrl() {
		return self::subfunctionUrl('noce');
	}

	public static function sysopUrl() {
		return self::subfunctionUrl('sysop');
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=msa)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=msa)::lgrpUrl', function($event) {
			$event->return = self::lgrpUrl($event->arguments(0));
		});
	}
}
