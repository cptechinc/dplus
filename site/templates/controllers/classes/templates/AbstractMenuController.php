<?php namespace Controllers\Templates;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;

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
		$functions = [];
		foreach (static::SUBFUNCTIONS as $key => $function) {
			if (empty($function['permission']) || self::pw('user')->hasPermissionCode($function['permission'])) {
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
		$html = '';
		$html .= self::pw('config')->twig->render('dplus-menu/bread-crumbs.twig');
		$html .= self::pw('config')->twig->render('dplus-menu/function-menu.twig', ['functions' => $functions]);
		return $html;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		// $m = self::pw('modules')->get('DpagesMpo');

		// $m->addHook('Page(pw_template=poadmn)::subfunctionUrl', function($event) {
		// 	$event->return = self::subfunctionUrl($event->arguments(0));
		// });
	}
}