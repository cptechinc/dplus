<?php namespace Controllers\Mci\Ciadmn;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
	// use OptionsIiQuery, OptionsIi;
// Dplus User Options
use Dplus\UserOptions;
// MVC Controllers
use Controllers\Abstracts\AbstractUserOptionsController;

class Cio extends AbstractUserOptionsController {
	const DPLUSPERMISSION = 'cio';
	const TITLE 		  = 'Customer Information Options';
	const SUMMARY		  = 'View / Edit User Access in CI';
	const SHOWONPAGE	  = 10;
	const BASE_MENU_CODE  = 'mci';

/* =============================================================
	4. URLs
============================================================= */
	public static function _url() {
		return self::pw('pages')->get('pw_template=cio')->url;
	}

	/**
	 * Return URL to Menu Page
	 * @return string
	 */
	public static function menuUrl() {
		return self::pw('pages')->get('pw_template=cio')->parent()->url;
	}

/* =============================================================
	7. Class / Module Getting
============================================================= */
	/**
	 * Return Manager
	 * @return UserOptions\AbstractManager
	 */
	public static function getManager() {
		return UserOptions\Cio::getInstance();
	}

/* =============================================================
	8. Supplemental
============================================================= */
	/**
	 * Return Menu Page Title
	 * @return string
	 */
	protected static function menuTitle() {
		return self::pw('pages')->get('pw_template=cio')->parent()->title;
	}
}
