<?php namespace Controllers\Mar;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Mvc Controllers
use Mvc\Controllers\Controller;

abstract class AbstractController extends Controller {
	const DPLUSPERMISSION = 'mar';

/* =============================================================
	URLs
============================================================= */
	public static function menuUrl() {
		$url = new Purl(self::pw('pages')->get('dplus_function=mar')->url);
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

	protected static function displayAlertUserPermission($data) {
		if (static::validateUserPermission() === false) {
			$writer = self::pw('modules')->get('HtmlWriter');
			$html   = self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Access Denied", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You don't have access to this function: ITM - " . static::PERMISSION_ITMP]);
			$html .= $writer->div('class=mt-3', $writer->a('class=btn btn-primary|href='.self::itmUrl($data->itemID), 'ITM'));
			return $html;
		}
		return '';
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
