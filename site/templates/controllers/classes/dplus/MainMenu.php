<?php namespace Controllers\Dplus;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus RecordLocker
use Dplus\RecordLocker;
// Dplus Function Routers
use Controllers\Routers;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class MainMenu extends AbstractController {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		self::deleteRecordLocks();
		if ($data->q) {
			self::redirectIfExists($data);
		}
		return self::list($data);
	}

	private static function redirectIfExists($data) {
		if (empty($data->q)) {
			return false;
		}
		$count = self::pw('pages')->find("dplus_function=$data->q")->count;

		if ($count === 1 && self::pw('user')->has_function($data->q)) {
			self::pw('session')->redirect(self::pw('pages')->get("dplus_function=$data->q")->url, $http301 = false);
		}

		$routers = new Routers\Factory();

		if ($routers->exists($data->q)) {
			if (self::pw('user')->has_function($data->q)) {
				$routers->route($data->q);
			}
		}
	}

	private static function list($data) {
		$permitted = implode("|", self::pw('user')->get_functions());
		$menu = self::pw('pages')->get('/');
		self::pw('page')->headline = "Menu";
		$results = $menu->children("template=dplus-menu|warehouse-menu, dplus_function=$permitted");

		if ($data->q) {
			self::pw('page')->headline = "Searching for functions that match '$data->q'";
			$results = self::pw('pages')->find("dplus_function%=$data->q");
			$results->filter("dplus_function=$permitted");
		}
		return self::pw('config')->twig->render('dplus-menu/menu-search-page.twig', ['page' => $menu, 'items' => $results]);
	}

/* =============================================================
	Supplemental
============================================================= */
	private static function deleteRecordLocks() {
		$locker = new RecordLocker\User();
		$locker->deleteLocks();
	}
}
