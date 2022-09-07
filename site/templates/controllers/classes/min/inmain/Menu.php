<?php namespace Controllers\Min\Inmain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Mvc Controllers
use Controllers\Min\Base;

class Menu extends Base {
	const DPLUSPERMISSION = 'inmain';
	const TITLE = 'Maintenance';
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
			'permission' => 'igpm',
			'title'      => 'Inventory Price Code',
			'summary'    => 'View / Edit Inventory Price Code'
		],
		'iplm' => [
			'name'       => 'iplm',
			'permission' => 'iplm',
			'title'      => 'Inventory Product Line Code',
			'summary'    => 'View / Edit Inventory Product Line Code'
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
			'permission' => 'iwhm',
			'title'      => 'Warehouse',
			'summary'    => 'View / Edit Warehouse'
		],
		'msdsm' => [
			'name'       => 'msdsm',
			'permission' => 'msdsm',
			'title'      => 'Material Safety Data Sheet Code',
			'summary'    => 'View / Edit Material Safety Data Sheet Code'
		],
		'spit' => [
			'name'       => 'spit',
			'permission' => 'spit',
			'title'      => 'Special Item Code',
			'summary'    => 'View / Edit Special Item Code'
		],
		'stcm' => [
			'name'       => 'stcm',
			'permission' => 'stcm',
			'title'      => 'Stock Code',
			'summary'    => 'View / Edit Stock Code'
		],
		'tarm' => [
			'name'       => 'tarm',
			'permission' => 'tarm',
			'title'      => 'Tariff Code',
			'summary'    => 'View / Edit Tariff Code'
		],
		'umm' => [
			'name'       => 'umm',
			'permission' => 'umm',
			'title'      => 'Unit of Measure Code',
			'summary'    => 'View / Edit Unit of Measure Code'
		],
		'upcx' => [
			'name'       => 'upcx',
			'permission' => 'upcx',
			'title'      => 'UPC Item X-Ref',
			'summary'    => 'View / Edit UPC Item X-Refs'
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
		$url->path->add('inmain');
		return $url->getUrl();
	}
	
	public static function menuUrl() {
		$url = new Purl(parent::menuUrl());
		$url->path->add('inmain');
		return $url->getUrl();
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::menuUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
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
	Displays
============================================================= */
	private static function menu($data) {
		$functions = [];
		foreach (self::SUBFUNCTIONS as $key => $function) {
			if (empty($function['permission']) || self::pw('user')->hasPermissionCode($function['permission'])) {
				$functions[$key] = $function;
			}
		}
		self::pw('page')->show_breadcrumbs = true;
		self::initHooks();
		return self::pw('config')->twig->render('dplus-menu/function-menu.twig', ['functions' => $functions]);
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
