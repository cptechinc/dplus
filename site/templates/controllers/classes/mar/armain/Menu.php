<?php namespace Controllers\Mar\Armain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Mvc Controllers
use Controllers\Mar\AbstractController as Base;

class Menu extends Base {
	const DPLUSPERMISSION = 'armain';
	const TITLE = 'Maintenance';
	const SUBFUNCTIONS = [
		'ccm' => [
			'name'       => 'ccm',
			'permission' => 'ccm',
			'title'      => 'Customer Commission Code',
			'summary'    => 'View / Edit Customer Commission Codes'
		],
		'cocom' => [
			'name'       => 'cocom',
			'permission' => 'cocom',
			'title'      => 'Country Code',
			'summary'    => 'View / Edit Country Codes'
		],
		'cpm' => [
			'name'       => 'cpm',
			'permission' => 'cpm',
			'title'      => 'Customer Price Code',
			'summary'    => 'View / Edit Customer Price Codes'
		],
		'crcd' => [
			'name'       => 'crcd',
			'permission' => 'crcd',
			'title'      => 'Credit Card Code',
			'summary'    => 'View / Edit Credit Card  Codes'
		],
		'crtm' => [
			'name'       => 'crtm',
			'permission' => 'crtm',
			'title'      => 'Customer Route Code',
			'summary'    => 'View / Edit Customer Route Codes'
		],
		'cuc' => [
			'name'       => 'cuc',
			'permission' => 'cuc',
			'title'      => 'Customer User Code',
			'summary'    => 'View / Edit Customer User Codes'
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
			'permission' => 'sic',
			'title'      => 'Standard Industrial Class',
			'summary'    => 'View / Edit AR Standard Industrial Class'
		],
		'spgpm' => [
			'name'       => 'spgpm',
			'permission' => 'spgpm',
			'title'      => 'Salesperson Group Code',
			'summary'    => 'View / Edit AR Salesperson Group Codes'
		],
		'suc' => [
			'name'       => 'suc',
			'permission' => 'suc',
			'title'      => 'Ship-to User Code',
			'summary'    => 'View / Edit AR Ship-to User Codes'
		],
		'tm' => [
			'name'       => 'tm',
			'permission' => Tm::DPLUSPERMISSION,
			'title'      => Tm::TITLE,
			'summary'    => Tm::SUMMARY
		],
		'worm' => [
			'name'       => 'worm',
			'permission' => 'worm',
			'title'      => 'Write-Off Reason Code',
			'summary'    => 'View / Edit Write-Off ReasonCodes'
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
		return self::menu($data);
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		$url = new Purl(parent::menuUrl());
		$url->path->add('armain');
		return $url->getUrl();
	}
	
	public static function menuUrl() {
		return self::url();
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::menuUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
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
	Displays
============================================================= */
	private static function menu($data) {
		$functions = [];
		foreach (self::SUBFUNCTIONS as $key => $function) {
			if (empty($function['permission']) || self::pw('user')->hasPermissionCode($function['permission'])) {
				$functions[$key] = $function;
			}
		}
		self::initHooks();
		return self::pw('config')->twig->render('dplus-menu/function-menu.twig', ['functions' => $functions]);
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
