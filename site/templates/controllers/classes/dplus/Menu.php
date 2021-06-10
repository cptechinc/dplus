<?php namespace Controllers\Dplus;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Menu extends AbstractController {
	public static function index($data) {
		$page = self::pw('page');
		$user = self::pw('user');

		$permission = empty($page->dplus_function) ? $page->dplus_permission : $page->dplus_function;
		$hasPermission = $user->has_function($permission) || empty($permission);

		if ($hasPermission == false) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: $permission"]);
			return $page->body;
		}
		$config = self::pw('config');

		$permission_list = implode("|", $user->get_functions());
		$page->pagetitle = "Menu: $page->title";
		$items = $page->children("template!=redir|dplus-json, dplus_function=$permission_list");

		$page->body .= $config->twig->render('dplus-menu/menu-search-form.twig', ['page' => self::pw('pages')->get('template=menu'), 'items' => $items]);
		$page->body .= $config->twig->render('dplus-menu/menu-list.twig', ['page' => $page, 'items' => $items]);
		return $page->body;
	}
}
