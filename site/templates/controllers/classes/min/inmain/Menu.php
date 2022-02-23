<?php namespace Controllers\Min\Inmain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
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
			'permission' => 'csccm',
			'title'      => 'Customer Stocking Cell',
			'summary'    => 'View / Edit Customer Stocking Cell'
		],
		'iasm' => [
			'name'       => 'iasm',
			'permission' => 'iasm',
			'title'      => 'Inventory Assortment Code',
			'summary'    => 'View / Edit Inventory Assortment Code'
		],
		'igcm' => [
			'name'       => 'igcm',
			'permission' => 'igcm',
			'title'      => 'Inventory Commission Code',
			'summary'    => 'View / Edit Inventory Commission Code'
		],
		'igm' => [
			'name'       => 'igm',
			'permission' => 'igm',
			'title'      => 'Inventory Group Code',
			'summary'    => 'View / Edit Inventory Group Code'
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
	public static function menuUrl() {
		$url = new Purl(parent::menuUrl());
		$url->path->add('main');
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

/* =============================================================
	Displays
============================================================= */
	private static function menu($data) {
		$functions = [];
		foreach (self::SUBFUNCTIONS as $key => $function) {
			if (empty($function['permission']) || self::pw('user')->hasPermission($function['permission'])) {
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
		$m = self::pw('modules')->get('DpagesMin');

		$m->addHook('Page(pw_template=inmain)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});
	}
}
