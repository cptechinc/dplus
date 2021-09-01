<?php namespace Controllers\Dplus;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
// Dplus RecordLocker
use Dplus\RecordLocker;

class Menu extends AbstractController {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$page   = self::pw('page');

		if (self::validateUserPermission() === false) {
			return self::notPermittedDisplay();
		}

		self::deleteRecordLocks();

		$permission_list = implode("|", self::pw('user')->get_functions());
		$page->pagetitle = "Menu: $page->title";
		$items = $page->children("template!=redir|dplus-json, dplus_function=$permission_list");
		return self::listDisplay($data, $items);
	}

/* =============================================================
	Displays
============================================================= */
	private static function listDisplay($data, $items) {
		$config = self::pw('config');
		$html = '';
		$html .= $config->twig->render('dplus-menu/menu-search-form.twig', ['page' => self::pw('pages')->get('template=menu'), 'items' => $items]);
		$html .= $config->twig->render('dplus-menu/menu-list.twig', ['items' => $items]);
		return $html;
	}

	protected static function notPermittedDisplay() {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: " . self::getPagePermission()]);
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function validateUserPermission() {
		$user = self::pw('user');
		$permission = self::getPagePermission();
		return empty($permission) || $user->has_function($permission);
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
