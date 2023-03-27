<?php namespace Controllers\Templates;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\User;
use ProcessWire\WireData;
// Dplus
use Dplus\Session\UserMenuPermissions;

/**
 * AbstractMenuController
 * 
 * Base Class for Rendering Menus
 */
abstract class AbstractMenuController extends AbstractController {
	const DPLUSPERMISSION = '';
	const TITLE   = '';
	const SUMMARY = '';
	const PARENT_MENU_CODE = '';
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
		$permittedList = UserMenuPermissions::instance()->list();

		foreach (static::SUBFUNCTIONS as $key => $function) {
			if (empty($function['permission']) || $permittedList->has($function['permission'])) {
				$functions[$key] = $function;
			}
		}
		return static::renderMenu($data, $functions);
	}
/* =============================================================
	2. Validations / Permissions / Initializations
============================================================= */
	/**
	 * Return if User has Permission based off Dplus Permission Code
	 * @param  User|null $user
	 * @return bool
	 */
	public static function validateUserPermission(User $user = null) {
		if (static::validateUserMenuPermission($user) === false) {
			return false;
		}
		return parent::validateUserPermission($user);
	}

	/**
	 * Return if User has Permission to the Menu this Menu is under
	 * @param  User|null $user
	 * @return bool
	 */
	public static function validateUserMenuPermission(User $user = null) {
		if (empty(static::PARENT_MENU_CODE)) {
			return true;
		}
		return UserMenuPermissions::instance()->canAccess(static::PARENT_MENU_CODE);
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