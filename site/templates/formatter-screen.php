<?php
	$page->pagetitle = $page->title;
	$module_formatter = $modules->get($page->formatter);
	$page->body = $config->twig->render('screen-formatters/formatter-form.twig', ['page' => $page, 'module_formatter' => $module_formatter]);
	include __DIR__ . "/basic-page.php";
