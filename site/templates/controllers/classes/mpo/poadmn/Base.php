<?php namespace Controllers\Mpo\Poadmn;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

abstract class Base extends AbstractController {
	const DPLUSPERMISSION = 'poadmn';

/* =============================================================
	Indexes
============================================================= */


/* =============================================================
	URLs
============================================================= */
	public static function poadmnUrl() {
		return self::pw('pages')->get('pw_template=poadmn')->url;
	}

	public static function cnfmUrl() {
		$url = new Purl(self::pw('pages')->get('pw_template=poadmn')->url);
		$url->path->add('cnfm');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	protected static function displayUserNotPermitted() {
		if (self::validateUserPermission()) {
			return true;
		}
		$perm = self::DPLUSPERMISSION;
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: $perm"]);
	}

/* =============================================================
	Validator, Module Getters
============================================================= */
	public static function validateUserPermission(User $user = null) {
		if (empty(self::DPLUSPERMISSION)) {
			return true;
		}
		if (empty($user)) {
			$user = self::pw('user');
		}
		return $user->hasPermission(self::DPLUSPERMISSION);
	}
}
