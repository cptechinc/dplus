<?php namespace Controllers\Mar\Armain;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;

class Menu extends Base {
	const DPLUSPERMISSION = 'armain';
	const TITLE = 'Maintenance';
	const SUBFUNCTIONS = [
		'ccm' => [
			'name'       => 'ccm',
			'permission' => 'ccm',
			'title'      => 'Customer Commission Code',
			'summary'    => 'View / Edit Customer Commission Code'
		],
		'cmm' => [
			'name'       => 'cmm',
			'permission' => 'cmm',
			'title'      => 'Customer Maintenance',
			'summary'    => 'View / Edit Customer'
		],
		'cpm' => [
			'name'       => 'cpm',
			'permission' => 'cpm',
			'title'      => 'Customer Price Code',
			'summary'    => 'View / Edit Customer Price Code'
		],
		'crcd' => [
			'name'       => 'crcd',
			'permission' => 'crcd',
			'title'      => 'Credit Card',
			'summary'    => 'View / Edit Credit Card Code'
		],
		'crtm' => [
			'name'       => 'crtm',
			'permission' => 'crtm',
			'title'      => 'Customer Route Code',
			'summary'    => 'View / Edit Customer Route Code'
		],
		'ctm' => [
			'name'       => 'ctm',
			'permission' => 'ctm',
			'title'      => 'Customer Type Code',
			'summary'    => 'View / Edit Customer Type Code'
		],
		'cuc' => [
			'name'       => 'cuc',
			'permission' => 'cuc',
			'title'      => 'Customer User Code',
			'summary'    => 'View / Edit Customer User Code'
		],
		'roptm' => [
			'name'       => 'roptm',
			'permission' => 'roptm',
			'title'      => 'Accounts Receivable Optional Codes',
			'summary'    => 'View / Edit AR Optional Codes'
		],
		'spgpm' => [
			'name'       => 'spgpm',
			'permission' => 'spgpm',
			'title'      => 'Salesperson Group Code',
			'summary'    => 'View / Edit Salesperson Group Code'
		],
		'spm' => [
			'name'       => 'spm',
			'permission' => 'spm',
			'title'      => 'Salesperson Maintenance',
			'summary'    => 'View / Edit Salesperson'
		],
		'sic' => [
			'name'       => 'sic',
			'permission' => 'sic',
			'title'      => 'Standard Industrial Class',
			'summary'    => 'View / Edit Standard Industrial Class Code'
		],
		'suc' => [
			'name'       => 'suc',
			'permission' => 'suc',
			'title'      => 'Ship-To User Code',
			'summary'    => 'View / Edit Ship-To User Code'
		],
		'trm' => [
			'name'       => 'trm',
			'permission' => 'trm',
			'title'      => 'Customer Terms Code',
			'summary'    => 'View / Edit Customer Terms Code'
		],
		'worm' => [
			'name'       => 'worm',
			'permission' => 'worm',
			'title'      => 'Customer Write-Off Code',
			'summary'    => 'View / Edit Customer Write-Off Code'
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
	public static function marUrl() {
		return self::pw('pages')->get('dplus_function=mar')->url;
	}

	public static function armainUrl() {
		$url = new Purl(self::marUrl());
		$url->path->add('armain');
		return $url->getUrl();
	}

	public static function menuUrl() {
		return self::armainUrl();
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::armainUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function ccmUrl() {
		return self::subfunctionUrl('ccm');
	}

	public static function cmmUrl() {
		return self::subfunctionUrl('cmm');
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

	public static function ctmUrl() {
		return self::subfunctionUrl('ctm');
	}

	public static function cucUrl() {
		return self::subfunctionUrl('cuc');
	}

	public static function roptmUrl() {
		return self::subfunctionUrl('roptm');
	}

	public static function spgpmUrl() {
		return self::subfunctionUrl('spgpm');
	}

	public static function spmUrl() {
		return self::subfunctionUrl('spm');
	}

	public static function sicUrl() {
		return self::subfunctionUrl('sic');
	}

	public static function sucUrl() {
		return self::subfunctionUrl('suc');
	}

	public static function trmUrl() {
		return self::subfunctionUrl('trm');
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

		$m->addHook('Page(pw_template=armain)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=armain)::armainUrl', function($event) {
			$event->return = self::armainUrl($event->arguments(0));
		});
	}
}
