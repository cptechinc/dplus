<?php namespace Controllers\Map\Apmain;
// ProcessWire
use ProcessWire\User;
// Mvc Controllers
use Mvc\Controllers\Controller;

abstract class AbstractController extends Controller {
	const DPLUSPERMISSION = 'apmain';
/* =============================================================
	Displays
============================================================= */
	protected static function displayUserNotPermitted() {
		if (self::validateUserPermission()) {
			return true;
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
		return $user->has_function(static::DPLUSPERMISSION);
	}
}
