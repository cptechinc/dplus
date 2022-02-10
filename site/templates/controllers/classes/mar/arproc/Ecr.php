<?php namespace Controllers\Arproc;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Mvc Controllers
use Controllers\Arproc\Base;

class Ecr extends Base {
	const DPLUSPERMISSION = '';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['custID|text']);
		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
		}
		self::initHooks();
		if (empty($data->custID) === false) {
			
		}
		return self::selectCustomer($data);
	}

	private static function selectCustomer($data) {
		self::pw('page')->js .= self::pw('config')->twig->render('mar/arproc/ecr/customer-form/.js.twig');
		return self::displaySelectCustomer($data);
	}


/* =============================================================
	URLs
============================================================= */


/* =============================================================
	Displays
============================================================= */
	private static function displaySelectCustomer($data) {
		$html = '';
		$html .= self::pw('config')->twig->render('mar/arproc/ecr/customer-form/display.twig');
		return $html;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMar');

	}
}
