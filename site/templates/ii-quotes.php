<?php
	include_once('./ii-include.php');

	if ($itemquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/ii/bread-crumbs.twig', ['page' => $page, 'item' => $item]);
		$page->title = "$itemID Quotes";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['itemid'] != $itemID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_itemquotesURL($itemID));
			}
			$session->quotestry = 0;
			$module_formatter = $modules->get('SfIiQuotes');
			$module_formatter->init_formatter();
			$refreshurl = $page->get_itemquotesURL($itemID);
			$document_management = $modules->get('DocumentManagement');

			$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);
			$page->body .= $config->twig->render('items/ii/quotes/quotes.twig', ['page' => $page, 'itemID' => $itemID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint(), 'document_management' => $document_management]);
		} else {
			if ($session->quotestry > 3) {
				$page->headline = $page->title = "Quotes File could not be loaded";
				$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->quotestry++;
				$session->redirect($page->get_itemquotesURL($itemID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
