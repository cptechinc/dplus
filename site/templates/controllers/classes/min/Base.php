<?php namespace Controllers\Min;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

abstract class Base extends AbstractController {
	const DPLUSPERMISSION = 'min';

/* =============================================================
	URLs
============================================================= */
	public static function menuUrl() {
		$url = new Purl(self::pw('pages')->get('dplus_function=min')->url);
		return $url->getUrl();
	}

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
