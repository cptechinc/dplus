<?php namespace Controllers\Abstracts;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;
// Controllers
use Controllers\AbstractController;

/**
 * AbstractMenuController
 * 
 * Base Class for Rendering Menus
 */
abstract class AbstractMenuController extends AbstractController {
	const DPLUSPERMISSION = '';
	const TITLE   = '';
	const SUMMARY = '';
	const SUBFUNCTIONS = [
		// 'cnfm' => [
		// 	'name'       => 'cnfm',
		// 	'permission' => Cnfm::DPLUSPERMISSION,
		// 	'title'      => Cnfm::TITLE,
		// 	'summary'    => Cnfm::SUMMARY
		// ]
	];

/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		self::sanitizeParametersShort($data, []);
		if (static::validateUserPermission() === false) {
			return static::renderUserNotPermittedAlert();
		}
		self::pw('page')->show_breadcrumbs = false;
		static::initHooks();
		return static::menu($data);
	}

	protected static function menu(WireData $data) {
		$user  = self::pw('user');
		$functions = [];
		foreach (static::SUBFUNCTIONS as $key => $function) {
			if (empty($function['permission']) || $user->has_function($function['permission'])) {
				$functions[$key] = $function;
			}
		}
		return static::renderMenu($data, $functions);
	}

/* =============================================================
	URLs
============================================================= */
	abstract protected static function _url();

	public static function url() {
		return static::_url();
	}

	public static function menuUrl() {
		return static::url();
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::url());
		if (array_key_exists($key, static::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

/* =============================================================
	Render HTML
============================================================= */
	private static function renderMenu(WireData $data, array $functions) {
		return self::pw('config')->twig->render('menu/controller-menu/display.twig', ['functions' => $functions]);
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dplus');

		$m->addHook('Page::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});
	}
}