<?php
	$q_whsesession = WhsesessionQuery::create()->filterBySessionid(session_id());

	if ($q_whsesession->sessionExists(session_id())) {
		$whsesession = $q_whsesession->findOne();
		$q_whse   = WarehouseQuery::create()->filterByWhseid($whsesession->whseid);

		if ($q_whse->count()) {
			include('./dplus-function.php');
		} else {
			$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Warehouse not Found", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Warehouse '$whsesession->whseid' not available "]);
			include('./basic-page.php');
		}
	} else {
		$url = $page->get_loginURL();
		$modules->get('DplusRequest')->self_request($url);
		$session->redirect($page->url);
	}
