<?php namespace Controllers\Mgl\Glmain;
// ProcessWire
use ProcessWire\User;
// Controllers
use Controllers\AbstractController as Controller;

abstract class AbstractController extends Controller {
	const DPLUSPERMISSION = 'glmain';

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
		if (Menu::validateUserPermission($user) === false) {
			return false;
		}
		return parent::validateUserPermission($user);
	}
}
