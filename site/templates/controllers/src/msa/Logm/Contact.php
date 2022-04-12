<?php namespace Controllers\Msa\Logm;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use DplusUser;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Msa;
use Dplus\Msa\Logm as LogmManager;
use Dplus\Msa\Logm\Contact as LogmContactManager;
// Conrollers
use Controllers\Msa\Logm;

class Contact extends Logm {
	private static $logmContact;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['id|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		$logm = self::getLogm();

		if (empty($data->id) || $logm->exists($data->id) === false) {
			self::pw('session')->redirect(self::logmUrl(), $http301 = false);
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::user($data);
	}

	public static function handleCRUD($data) {
		$fields = ['id|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::logmUrl();
		$logm = self::getLogmContact();

		if ($data->action) {
			$logm->processInput(self::pw('input'));
			$url  = self::userEditUrl($data->id);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function user($data) {
		$logm = self::getLogm();
		$page = self::pw('page');
		$page->headline = "LOGM: $data->id Fax Defaults";

		if ($logm->exists($data->id) === false) {
			self::pw('session')->redirect(self::logmUrl($data->id), $http301 = false);
		}
		$user = $logm->user($data->id);
		$logm->lockrecord($data->id);

		self::initHooks();
		// $page->js .= self::pw('config')->twig->render('msa/logm/user/.js.twig', ['logm' => self::getLogm()]);
		$html = self::displayUser($data, $user);
		return $html;
	}

/* =============================================================
	URLs
============================================================= */


/* =============================================================
	Displays
============================================================= */
	private static function displayUser($data, DplusUser $user) {
		$config = self::pw('config');
		$logmContact = self::getLogmContact();

		$html  = '';
		$html .= '<div class="mb-3">' . self::displayLock($data) . '</div>';
		$html .= $config->twig->render('msa/logm/user/contact/display.twig', ['logm' => $logmContact, 'duser' => $user]);
		return $html;
	}

/* =============================================================
	Hooks
============================================================= */

/* =============================================================
	Supplemental
============================================================= */
	public static function getLogmContact() {
		if (empty(self::$logmContact)) {
			self::$logmContact = LogmContactManager::getInstance();
		}
		return self::$logmContact;
	}
}
