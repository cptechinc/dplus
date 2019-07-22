<?php
	$page->pagetitle = $page->title;
	$page->body = $config->twig->render('screen-formatters/menu.twig', ['page' => $page, 'items' => $page->children]);
	include __DIR__ . "/basic-page.php";
