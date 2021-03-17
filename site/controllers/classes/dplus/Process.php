<?php namespace Controllers\Dplus;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Process extends AbstractController {
	static $permitted = false;
	static $templateExists = false;
	static $template;

	public static function index($data) {
		$page   = self::pw('page');
		$user   = self::pw('user');
		$config = self::pw('config');

		$templateExists = self::templateExists();

		if (empty($page->pw_template) || $templateExists === false) {
			$page->headline = $page->title = "Cannot Render Page";
			$msg = empty($page->pw_template) ? 'Template is not defined' : 'Template can not be found';
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => $msg]);
			return $page->body;
		}

		$permission = empty($page->dplus_function) ? $page->dplus_permission : $page->dplus_function;
		$hasPermission = $user->has_function($permission) || empty($permission);

		if ($hasPermission === false) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: $permission"]);
			return $page->body;
		}
		self::$permitted = true;
		self::$templateExists = true;
		self::$template = self::templateFileName($page);
	}

	public static function templateExists() {
		$page = self::pw('page');
		$template = self::templateFileName($page);
		return file_exists("./$template");
	}

	public static function templateFileName(Page $page) {
		return str_replace('.php', '', $page->pw_template) . '.php';
	}
}
