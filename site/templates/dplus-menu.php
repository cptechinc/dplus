<?php
	if ($user->has_function($page->dplus_function) || empty($page->dplus_function)) {
		$permission_list = implode("|", $user->get_functions());
		$page->pagetitle = "Menu: $page->title";
		$items = $page->children("template!=redir, dplus_function=$permission_list");

		$page->body .= $config->twig->render('dplus-menu/menu-search-form.twig', ['page' => $pages->get('template=menu'), 'items' => $items]);
		$page->body .= $config->twig->render('dplus-menu/menu-list.twig', ['page' => $page, 'items' => $items]);
	} else {
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Function: $page->dplus_function"]);
	}

	include __DIR__ . "/basic-page.php";
