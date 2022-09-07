<?php namespace Controllers\Map\Apmain;
// Purl Library
use Purl\Url as Purl;

class Menu extends Base {
	const DPLUSPERMISSION = 'apmain';
	const TITLE = 'Maintenance';
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
		'mxrfe' => [
			'name'       => 'mxrfe',
			'permission' => 'mxrfe',
			'title'      => 'Mnfr / Competitor Item X-Ref',
			'summary'    => 'View / Edit Manufacturer / Competitor X-Ref'
		],
		'vtm' => [
			'name'       => 'vtm',
			'permission' => Vtm::DPLUSPERMISSION,
			'title'      => Vtm::TITLE,
			'summary'    => Vtm::SUMMARY
		],
		'vxm' => [
			'name'       => 'vxm',
			'permission' => 'vxm',
			'title'      => 'Vendor Item X-Ref',
			'summary'    => 'View / Edit Vendor Item X-Ref'
		],
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
	public static function mapUrl() {
		return self::pw('pages')->get('dplus_function=map')->url;
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::apmainUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function apmainUrl() {
		$url = new Purl(self::mapUrl());
		$url->path->add('apmain');
		return $url->getUrl();
	}

	public static function url() {
		return self::apmainUrl();
	}

	public static function menuUrl() {
		return self::apmainUrl();
	}

	public static function vtmUrl() {
		return self::subfunctionUrl('vtm');
	}

	public static function bumUrl() {
		return self::subfunctionUrl('bum');
	}

	public static function mxrfeUrl() {
		return self::subfunctionUrl('mxrfe');
	}

	public static function aoptmUrl() {
		return self::subfunctionUrl('aoptm');
	}

	public static function vxmUrl() {
		return self::subfunctionUrl('vxm');
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
		$html .= self::pw('config')->twig->render('dplus-menu/bread-crumbs.twig');
		$html .= self::pw('config')->twig->render('dplus-menu/function-menu.twig', ['functions' => $functions]);
		return $html;
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
			$event->return = self::apmainUrl($event->arguments(0));
		});
	}
}
