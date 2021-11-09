<?php namespace Controllers\Msa;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Mvc Controllers
use Controllers\Msa\Base;

class Menu extends Base {
	const DPLUSPERMISSION = 'msa';
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
		]
	];

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, []);
		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
		}
		self::initHooks();
		return self::menu($data);
	}


/* =============================================================
	URLs
============================================================= */
	public static function msaUrl() {
		return self::pw('pages')->get('template=test')->url;
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::msaUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function lgrpUrl() {
		return self::subfunctionUrl('lgrp');
	}

	public static function noceUrl() {
		return self::subfunctionUrl('noce');
	}

	public static function logmUrl() {
		return self::subfunctionUrl('logm');
	}

/* =============================================================
	Displays
============================================================= */
	private static function menu($data) {
		$functions = [];
		foreach (self::SUBFUNCTIONS as $key => $function) {
			if (empty($function['permission']) || self::pw('user')->hasPermissionCode($function['permission'])) {
				$functions[$key] = $function;
			}
		}
		return self::displayMenu($data, $functions);
	}

	private static function displayMenu($data, array $functions) {
		$html = '';
		// $html .= self::pw('config')->twig->render('dplus-menu/bread-crumbs.twig');
		$html .= self::pw('config')->twig->render('dplus-menu/function-menu.twig', ['functions' => $functions]);
		return $html;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(template=test)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(template=test)::lgrpUrl', function($event) {
			$event->return = self::lgrpUrl($event->arguments(0));
		});
	}
}
