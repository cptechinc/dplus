<?php
	$page->pagetitle = "Menu: $page->title";
	$page->body = $config->twig->render('dplus-menu/menu-list.twig', ['items' => $page->children()]);
	include __DIR__ . "/basic-page.php";