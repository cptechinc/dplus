<?php namespace Controllers\Mvi\Vi\Admin;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
	// use OptionsViQuery, OptionsVi;
// Dplus User Options
use Dplus\UserOptions;
// MVC Controllers
use Controllers\Abstracts\AbstractUserOptionsController;

class Vio extends AbstractUserOptionsController {
	const DPLUSPERMISSION = 'vio';
	const TITLE 		  = 'Vendor Information Options';
	const SUMMARY		  = 'View / Edit User Access in VI';
	const SHOWONPAGE	  = 10;
	const BASE_MENU_CODE  = 'mvi';

/* =============================================================
	4. URLs
============================================================= */
	public static function _url() {
		return self::pw('pages')->get('pw_template=vio')->url;
	}

	/**
	 * Return URL to Menu Page
	 * @return string
	 */
	public static function menuUrl() {
		return self::pw('pages')->get('pw_template=vio')->parent()->url;
	}

/* =============================================================
	7. Class / Module Getting
============================================================= */
	/**
	 * Return Manager
	 * @return UserOptions\AbstractManager
	 */
	public static function getManager() {
		return UserOptions\Vio::getInstance();
	}

/* =============================================================
	8. Supplemental
============================================================= */
	/**
	 * Return Menu Page Title
	 * @return string
	 */
	protected static function menuTitle() {
		return self::pw('pages')->get('pw_template=vio')->parent()->title;
	}
}
