<?php namespace Controllers\Aproc;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Mvc Controllers
use Controllers\Aproc\Base;

class Menu extends Base {
	const DPLUSPERMISSION = '';
	const TITLE = 'Accounts Receivable Processing';
	const SUBFUNCTIONS = [
		'ecr' => [
			'name'       => 'ecr',
			'permission' => 'ecr',
			'title'      => 'Cash Receipts',
			'summary'    => 'Enter / Change Cash Receipts'
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
	public static function arprocUrl() {
		return self::pw('pages')->get('pw_template=arproc')->url;
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::arprocUrl());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
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
		$m = self::pw('modules')->get('DpagesMar');

		$m->addHook('Page(pw_template=arproc)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=arproc)::arprocUrl', function($event) {
			$event->return = self::arprocUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=arproc)::glmainUrl', function($event) {
			$event->return = self::glmainUrl($event->arguments(0));
		});
	}
}
