<?php namespace Controllers\Dplus;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus 
use Dplus\Session\UserMenuPermissions;
use Dplus\RecordLocker;
// Dplus Function Routers
use Controllers\Routers;
// Mvc Controllers
use Mvc\Controllers\Controller;

class MainMenu extends Controller {
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

		$perimittedList = UserMenuPermissions::instance()->list();

		if ($count === 1 && $perimittedList->has($data->q)) {
			self::pw('session')->redirect(self::pw('pages')->get("dplus_function=$data->q")->url, $http301 = false);
		}

		$routers = new Routers\Factory();

		if ($routers->exists($data->q) && $perimittedList->has($data->q)) {
			$routers->route($data->q);;
		}
	}

	private static function list($data) {
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = "Menu";
		$menu = self::pw('pages')->get('/');

		$permittedList = UserMenuPermissions::instance()->list();
		$permitted = implode('|', array_merge($permittedList->getKeys(), ['']));
		
		$results = $menu->children("template=dplus-menu|warehouse-menu, dplus_function=$permitted,sort=dplus_function");

		if ($data->q) {
			self::pw('page')->headline = "Searching for '$data->q'";
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
