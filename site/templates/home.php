<?php
	$page->body = $config->twig->render('dplus-menu/menu-search-page.twig', ['items' => $pages->get('/')->children('template=dplus-menu|warehouse-menu')]);
	include __DIR__ . "/basic-page.php";
