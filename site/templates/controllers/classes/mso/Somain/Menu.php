<?php namespace Controllers\Mso\Somain;
// Purl URI Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page;

class Menu extends AbstractController {
	const DPLUSPERMISSION = 'somain';
	const TITLE = 'Maintenance';
	const SUBFUNCTIONS = [
		'cxm' => [
			'name'       => 'cxm',
			'permission' => 'cxm',
			'title'      => 'Customer Item X-Ref',
			'summary'    => 'View / Edit Customer Item X-Ref'
		],
		'lsm' => [
			'name'       => 'lsm',
			'permission' => Lsm::DPLUSPERMISSION,
			'title'      => Lsm::TITLE,
			'summary'    => Lsm::SUMMARY
		],
		'mfcm' => [
			'name'       => 'mfcm',
			'permission' => Mfcm::DPLUSPERMISSION,
			'title'      => Mfcm::TITLE,
			'summary'    => Mfcm::SUMMARY
		],
		'rgarc' => [
			'name'       => 'rgarc',
			'permission' => Rgarc::DPLUSPERMISSION,
			'title'      => Rgarc::TITLE,
			'summary'    => Rgarc::SUMMARY
		],
		'rgasc' => [
			'name'       => 'rgasc',
			'permission' => Rgasc::DPLUSPERMISSION,
			'title'      => Rgasc::TITLE,
			'summary'    => Rgasc::SUMMARY
		],
		'soptm' => [
			'name'       => 'soptm',
			'permission' => 'soptm',
			'title'      => 'Sales Order Optional Code',
			'summary'    => 'View / Edit Sales Order Optional Code'
		],
	];

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, []);
		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}
		self::initHooks();
		return self::menu($data);
	}

/* =============================================================
	URLs
============================================================= */
	public static function msoUrl() {
		return self::pw('pages')->get('dplus_function=mso')->url;
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::somainUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function somainUrl() {
		$url = new Purl(self::msoUrl());
		$url->path->add('somain');
		return $url->getUrl();
	}

	public static function menuUrl() {
		return self::somainUrl();
	}

	public static function soptmUrl() {
		return self::subfunctionUrl('soptm');
	}

	public static function lsmUrl() {
		return self::subfunctionUrl('lsm');
	}

	public static function mfcmUrl() {
		return self::subfunctionUrl('mfcm');
	}

	public static function rgarcUrl() {
		return self::subfunctionUrl('rgarc');
	}

	public static function rgascUrl() {
		return self::subfunctionUrl('rgasc');
	}

	public static function cxmUrl() {
		return self::subfunctionUrl('cxm');
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

		$m->addHook('Page(pw_template=somain)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=somain)::somainUrl', function($event) {
			$event->return = self::somainUrl($event->arguments(0));
		});
	}
}
