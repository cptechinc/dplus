<?php namespace Controllers\Templates;
// ProcessWire
use ProcessWire\User;
use ProcessWire\WireData;
// Dplus
use Dplus\Session\UserMenuPermissions;
// Mvc Controllers
use Mvc\Controllers\Controller;


/**
 * Abstract Controller
 * 
 * Base Controller class for HTTP requests
 */
abstract class AbstractController extends Controller {
	const DPLUSPERMISSION = '';
	const TITLE   = '';
	const SUMMARY = '';

/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {}
	
	public static function handleCRUD(WireData $data) {
		self::pw('session')->redirect(static::url(), $http301 = false);
	}

/* =============================================================
	URLs
============================================================= */
	abstract public static function url();

/* =============================================================
	Render HTML
============================================================= */
	protected static function renderUserNotPermittedAlert() {
		if (static::validateUserPermission()) {
			return '';
		}
		$perm = static::DPLUSPERMISSION;
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: $perm"]);
	}

/* =============================================================
	Validator, Module Getters
============================================================= */
	public static function validateUserPermission(User $user = null) {
		if (empty(static::DPLUSPERMISSION)) {
			return true;
		}
		if (empty($user)) {
			$user = self::pw('user');
		}
		$MCP = UserMenuPermissions::instance();
		return $MCP->canAccess(static::DPLUSPERMISSION);
	}
}