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
