<?php
	include_once('./ii-include.php');

	use ItemsearchQuery, Itemsearch;
	use WarehouseQuery, Warehouse;

	$warehouses = WarehouseQuery::create()->find();

	if ($itemquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/ii/bread-crumbs.twig', ['page' => $page, 'item' => $item]);
		$page->title = "$itemID Requirements";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['itemid'] != $itemID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_itemrequirementsURL($itemID));
			}
			$session->requirementstry = 0;

			$refreshurl = $page->get_itemrequirementsURL($itemID);
			$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);
			$page->body .= $config->twig->render('items/ii/requirements/requirements-form.twig', ['page' => $page, 'itemID' => $itemID, 'warehouses' => $warehouses, 'module_ii' => $module_ii, 'view' => $json['reqavl'], 'whse' => $json['whse']]);
			$page->body .= $config->twig->render('items/ii/requirements/requirements.twig', ['page' => $page, 'view' => $module_ii->get_requirementsoptions()[$json['reqavl']], 'json' => $json, 'module_json' => $module_json]);
		} else {
			if ($session->requirementstry > 3) {
				$page->headline = $page->title = "Requirements File could not be loaded";
				$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->requirementstry++;
				$session->redirect($page->get_itemrequirementsURL($itemID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
