<?php namespace Controllers\Mwm\Inventory\Mlot;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Document Management
use Dplus\DocManagement\Finders\Lt\Img as Docm;
// Dplus Inventory Search
use Dplus\Wm\Inventory\Search;
// Mvc Controllers
use Controllers\Wm\Base;

class Img extends Base {
	const DPLUSPERMISSION = 'wm';
	const TITLE = 'Lot Images';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['scan|text', 'lotserial|text']);
		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
		}
		if (empty($data->scan) === false) {
			return self::scan($data);
		}
		if (empty($data->lotserial) === false) {
			return self::lotserial($data);
		}
		return self::displayScanForm($data);
	}

	private static function scan($data) {
		Search::getInstance()->requestSearch($data->scan);
		self::initHooks();
		return self::displayScanResults($data);
	}

	private static function lotserial($data) {
		Search::getInstance()->requestSearch($data->lotserial);
		self::initHooks();
		return self::displayLotserial($data);
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayScanForm($data) {
		return self::pw('config')->twig->render('warehouse/inventory/mvc/form.twig');
	}

	private static function displayScanResults($data) {
		$inventory = Search::getInstance();
		return self::pw('config')->twig->render('warehouse/inventory/mlot/img/scan/results.twig', ['inventory' => $inventory]);
	}

	private static function displayLotserial($data) {
		$inventory = Search::getInstance();
		$lotserial = $inventory->getLotserial($data->lotserial);
		$docm = self::getDocm();
		return self::pw('config')->twig->render('warehouse/inventory/mlot/img/lotserial/display.twig', ['lotserial' => $lotserial]);
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function lotserialUrl($lotserial) {
		$url = new Purl(Menu::imgUrl());
		$url->query->set('lotserial', $lotserial);
		return $url->getUrl();
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=whse-mlot)::lotserialUrl', function($event) {
			$event->return = self::lotserialUrl($event->arguments(0));
		});
	}

	public static function getDocm() {
		return new Docm();
	}
}
