<?php
	$page->pagetitle = "Menu: $page->title";
	$page->body = $config->twig->render('dplus-menu/menu-list.twig', ['page' => $page, 'items' => $page->children('template!=redir')]);
	include __DIR__ . "/basic-page.php";
