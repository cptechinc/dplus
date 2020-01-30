<?php
	$permission_list = implode("|", $user->get_functions());

	if ($input->get->q) {
		$code = $input->get->text('q');

		$actualcount = $pages->find("dplus_function=$code")->count;

		// COUNT HOW MANY FUNCTIONS ACTUALLY EXIST WITH THIS CODE
		if ($actualcount == 1) {
			// CHECK IF USER HAS ACCESS TO THIS FUNCTION
			if ($user->has_function($code)) {
				$session->redirect($pages->get("dplus_function=$code")->url, $http301 = false);
			} else {
				$resultscount = 0;
				$page->pagetitle = "Searching for functions that match '$code'";
				$page->body = $config->twig->render('dplus-menu/menu-search-page.twig', ['page' => $page, 'items' => new ProcessWire\PageArray()]);
			}
		} else {
			$mainmenu = $pages->get('/');
			$page->pagetitle = "Searching for functions that match '$code'";
			$functions = $mainmenu->children("dplus_function~=$code");
			$functions->filter("dplus_function=$permission_list");
			$page->body = $config->twig->render('dplus-menu/menu-search-page.twig', ['page' => $page, 'items' => $functions]);
		}
	} else {
		$mainmenu = $pages->get('/');
		$page->pagetitle = "Menu";
		$menus = $mainmenu->children("template=dplus-menu|warehouse-menu, dplus_function=$permission_list");
		$page->body = $config->twig->render('dplus-menu/menu-search-page.twig', ['page' => $page, 'items' => $menus]);
	}
	include __DIR__ . "/basic-page.php";
