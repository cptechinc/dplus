<?php
	$permission_list = implode("|", $user->get_functions());
	$page->pagetitle = "Menu: $page->title";
	$items = $page->children("template!=redir, dplus_function=$permission_list");
	$page->body = $config->twig->render('dplus-menu/menu-list.twig', ['page' => $page, 'items' => $items]);
	include __DIR__ . "/basic-page.php";
