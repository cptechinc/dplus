<?php namespace Controllers\Templates;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\Page;
use ProcessWire\PageArray;
use ProcessWire\User;
use ProcessWire\WireData;
// Dplus RecordLocker
use Dplus\RecordLocker;

/**
 * AbstractMenuController
 * 
 * Base Class for Rendering Menus
 */
abstract class AbstractPageMenuController extends AbstractController {
	const DPLUSPERMISSION = '';
	const TITLE   = '';
	const SUMMARY = '';

/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		self::sanitizeParametersShort($data, []);
		if (static::validateUserPermission() === false) {
			return static::renderUserNotPermittedAlert();
		}
		static::initHooks();
		self::deleteRecordLocks();
		return static::menu($data);
	}

	protected static function menu(WireData $data) {
		$permission_list = implode("|", self::pw('user')->get_functions());
		$page = self::pw('page');
		$page->pagetitle = "Menu: $page->title";
		$items = $page->children("template!=redir|dplus-json, dplus_function=$permission_list");

		return static::renderMenu($data, $items);
	}

/* =============================================================
	URLs
============================================================= */
	abstract protected static function _url();

	public static function url() {
		return static::_url();
	}

	public static function menuUrl() {
		return static::url();
	}

	public static function subfunctionUrl($key) {
		$url = new Purl(self::url());
		$url->path->add($key);
		return $url->getUrl();
	}

/* =============================================================
	Render HTML
============================================================= */
	protected static function renderMenu(WireData $data, PageArray $pages) {
		$html = self::pw('config')->twig->render('dplus-menu/display.twig', ['items' => $pages]);
		return $html;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		// $m = self::pw('modules')->get('DpagesMpo');

		// $m->addHook('Page(pw_template=poadmn)::subfunctionUrl', function($event) {
		// 	$event->return = self::subfunctionUrl($event->arguments(0));
		// });
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function validateUserPermission(User $user = null) {
		if (empty($user)) {
			$user = self::pw('user');
		}
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