<?php namespace Controllers;
// Base PHP
use ReflectionClass;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\User;
use ProcessWire\Page;
// Mvc Controllers
use Mvc\Controllers\Controller;

abstract class AbstractController extends Controller {
	const DPLUSPERMISSION = '';
	const ALLOW_AJAX = false;

/* =============================================================
	1. Indexes
============================================================= */

/* =============================================================
	2. Validations / Permissions / Initializations
============================================================= */
	/**
	 * Return if User has Permission based off Dplus Permission Code
	 * @param  User|null $user
	 * @return bool
	 */
	public static function validateUserPermission(User $user = null) {
		if (empty(static::DPLUSPERMISSION)) {
			return true;
		}
		if (empty($user)) {
			$user = self::pw('user');
		}
		return $user->has_function(static::DPLUSPERMISSION);
	}

	/**
	 * Return if User has Permission to Access Page
	 * @param  Page|null $page
	 * @return bool
	 */
	public static function validateUserPagePermission(Page $page = null) {
		$user = self::pw('user');
		$permission = self::getPagePermission($page);
		return empty($permission) || $user->has_function($permission);
	}

	/**
	 * Return Page's Permission Code
	 * @param  Page|null $page
	 * @return void
	 */
	public static function getPagePermission(Page $page = null) {
		$page = $page ? $page : self::pw('page');
		return $page->permissioncode;
	}

/* =============================================================
	3. Data Fetching / Requests / Retrieval
============================================================= */

/* =============================================================
	4. URLs
============================================================= */

/* =============================================================
	5. Displays
============================================================= */

/* =============================================================
	6. HTML Rendering
============================================================= */
	/**
	 * Return User is Not Permitted Alert
	 * @return string
	 */
	protected static function renderUserNotPermittedAlert() {
		if (static::validateUserPermission()) {
			return '';
		}
		$perm = static::DPLUSPERMISSION;
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: $perm"]);
	}
	
/* =============================================================
	7. Class / Module Getters
============================================================= */

/* =============================================================
	8. Supplemental
============================================================= */
	public static function addAjaxNoceJs() {
		// self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/ajax-noce-modal.js'));
	}

	/**
	 * Return Reflection Class
	 * @return ReflectionClass
	 */
	protected static function getStaticReflectionClass() {
		return new ReflectionClass(static::class);
	}

	/**
	 * Return Namespace as path
	 * @return string
	 */
	protected static function getNamespaceAsPath() {
		$insp = static::getStaticReflectionClass();
		$ns = $insp->getNamespaceName();
		$ns = preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $ns);
		$ns = strtolower($ns);
		$ns = ltrim($ns, 'controllers\\');
		$ns = str_replace("\\", '/', $ns);
		return $ns;
	}

	/**
	 * Return Class Name as path
	 * @return string
	 */
	protected static function getClassNameAsPath() {
		$insp = static::getStaticReflectionClass();
		$class = $insp->getShortName();
		$class = preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $class);
		$class = strtolower($class);
		return $class;
	}

/* =============================================================
	9. Hooks / Object Decorating
============================================================= */

/* =============================================================
	10. Sessions
============================================================= */
}
