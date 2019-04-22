<?php
	// TODO Permission Checking

	if ($input->get->q) {
		$code = $input->get->text('q');
		$resultscount = $pages->find("dplus_function=$code")->count;

		if ($resultscount == 1) {
			$session->redirect($pages->get("dplus_function=$code")->url, $http301 = false);
		} else {
			$mainmenu = $pages->get('/');
			$page->pagetitle = "Searching for functions that match '$code'";
			$page->body = $config->twig->render('dplus-menu/menu-search-page.twig', ['items' => $mainmenu->children("dplus_function~=$code")]);
		}
	} else {
		$mainmenu = $pages->get('/');
		$page->pagetitle = "Menu";
		$page->body = $config->twig->render('dplus-menu/menu-search-page.twig', ['items' => $mainmenu->children('template=dplus-menu|warehouse-menu')]);
	}
	include __DIR__ . "/basic-page.php";
