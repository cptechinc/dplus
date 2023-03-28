<?php namespace Controllers\Dplus;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Mvc Controllers
use Mvc\Controllers\Controller;
// Dplus
use Dplus\Session\UserMenuPermissions;
use Dplus\RecordLocker;

class Menu extends Controller {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$page   = self::pw('page');

		if (self::validateMenuPermission() === false || self::validateUserPermission() === false) {
			return self::notPermittedDisplay();
		}

		self::deleteRecordLocks();

		$permittedList = UserMenuPermissions::instance()->list();
		$permitted = implode('|', array_merge($permittedList->getKeys(), ['']));
		// $page->pagetitle = "Menu: $page->title";
		$items = $page->children("template!=redir|dplus-json, dplus_function=$permitted");
		return self::listDisplay($data, $items);
	}

/* =============================================================
	Displays
============================================================= */
	private static function listDisplay($data, $items) {
		$config = self::pw('config');
		$html = $config->twig->render('dplus-menu/display.twig', ['items' => $items]);
		return $html;
	}

	public static function notPermittedDisplay() {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: " . self::getPagePermission()]);
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function validateUserPermission() {
		$user = self::pw('user');
		$permission = self::getPagePermission();
		$MCP = UserMenuPermissions::instance();
		return empty($permission) || $MCP->canAccess($permission);
	}

	public static function validateMenuPermission() {
		$page   = self::pw('page');

		foreach ($page->parents('template=dplus-menu|warehouse-menu') as $parent) {
			$code = $parent->dplus_function ? $parent->dplus_function : $parent->dplus_permission;

			if (empty($code) === false && UserMenuPermissions::instance()->canAccess($code) === false) {
				return false;
			}
		}
		return true;
	}

	public static function getPagePermission(Page $page = null) {
		$page = $page ? $page : self::pw('page');
		return empty($page->dplus_function) ? $page->dplus_permission : $page->dplus_function;
	}

	public static function templateExists(Page $page = null) {
		$page = $page ? $page : self::pw('page');
		$template = self::templateFileName($page);
		return file_exists("./$template");
	}

	public static function templateFileName(Page $page) {
		return str_replace('.php', '', $page->pw_template) . '.php';
	}

	private static function deleteRecordLocks() {
		$locker = new RecordLocker\User();
		$locker->deleteLocks();
	}
}
