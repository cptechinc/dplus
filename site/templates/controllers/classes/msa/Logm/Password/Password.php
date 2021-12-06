<?php namespace Controllers\Msa\Logm;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use DplusUser;
// Dplus Filters
use Dplus\Filters;
// Dplus Msa
use Dplus\Msa;
use Dplus\Msa\Logm\Password as LogmPassword;
// Conrollers
use Controllers\Msa\Logm;

class Password extends Logm {
	private static $pswd;

/* =============================================================
	Indexes
============================================================= */
	public static function handleCRUD($data) {
		$fields = ['id|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::logmUrl();
		$logm = self::getLogmPassword();

		if ($data->action) {
			$logm->processInput(self::pw('input'));
			$url  = self::userEditUrl($data->id);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

/* =============================================================
	URLs
============================================================= */

/* =============================================================
	Displays
============================================================= */

/* =============================================================
	Hooks
============================================================= */

/* =============================================================
	Supplemental
============================================================= */
	public static function getLogmPassword() {
		if (empty(self::$pswd)) {
			self::$pswd = LogmPassword::getInstance();
		}
		return self::$pswd;
	}
}
