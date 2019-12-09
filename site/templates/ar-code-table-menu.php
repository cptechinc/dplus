<?php
	$module_codetables = $modules->get('CodeTablesAr');
	$codetables = $module_codetables->get_codetables();

	$page->body .= $config->twig->render('code-tables/tables-list.twig', ['page' => $page, 'tables' => $codetables]);
	include __DIR__ . "/basic-page.php";
