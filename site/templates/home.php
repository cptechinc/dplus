<?php
	if (empty(wire('dplusdata'))) {
		$page->headline = $page->title = "Cannot Connect to Dplus Database";
		$page->body  = "Error has been logged";
	} else {
		$page->body = $config->twig->render('dplus-menu/menu-list.twig', ['items' => $pages->get('/')->find('template=dplus-menu')]);
		include __DIR__ . "/basic-page.php";
	}
	


