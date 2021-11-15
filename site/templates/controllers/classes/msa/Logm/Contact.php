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
// Conrollers
use Controllers\Msa\Logm;

class Contact extends Logm {


/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['id|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->id) === false) {
			return self::user($data);
		}
	}

	public static function handleCRUD($data) {
		$fields = ['id|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::logmUrl();
		$logm = self::getLogm();

		if ($data->action) {
			$logm->processInput(self::pw('input'));
			$url  = self::logmUrl($data->id);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function user($data) {
		$logm = self::getLogm();
		$page = self::pw('page');
		$page->headline = "LOGM: $data->id";

		if ($logm->exists($data->id) === false) {
			$page->headline = "LOGM: Creating New User";
		}
		$user = $logm->getOrCreate($data->id);

		if ($user->isNew() === false) {
			$logm->lockrecord($data->id);
		}
		self::initHooks();
		$page->js .= self::pw('config')->twig->render('msa/logm/user/.js.twig', ['logm' => self::getLogm()]);
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
		$logm   = self::getLogm();

		$html  = '';
		$html .= '<div class="mb-3">' . self::displayLock($data) . '</div>';
		$html .= $config->twig->render('msa/logm/user.twig', ['logm' => $logm, 'duser' => $user]);
		return $html;
	}

/* =============================================================
	Hooks
============================================================= */

/* =============================================================
	Supplemental
============================================================= */

}
