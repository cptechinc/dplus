<?php
	include_once('./ii-include.php');

	use ItemsearchQuery, Itemsearch;

	if ($itemquery->count()) {
		$page->title = "$itemID Costing";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			$session->costingtry = 0;
			$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID]);
			$page->body .= $config->twig->render('items/ii/costing/item-info.twig', ['page' => $page, 'json' => $json]);
			$page->body .= $config->twig->render('items/ii/costing/costing-screen.twig', ['page' => $page, 'json' => $json, 'module_json' => $module_json]);
		} else {
			if ($session->costingtry > 3) {
				$page->headline = $page->title = "Costing File could not be loaded";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->costingtry++;
				$session->redirect($page->get_itemcostingURL($itemID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
