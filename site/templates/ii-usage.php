<?php
	include_once('./ii-include.php');

	use ItemsearchQuery, Itemsearch;
	$module_usage = $modules->get('IiUsage');

	if ($itemquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/ii/bread-crumbs.twig', ['page' => $page, 'item' => $item]);
		$page->title = "$itemID Usage";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);
		$config->styles->append('//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css');
		$config->scripts->append('//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js');
		$config->scripts->append('//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js');
		$config->scripts->append(hash_templatefile('scripts/lib/moment.js'));

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['itemid'] != $itemID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_itemusageURL($itemID));
			}
			$session->usagetry = 0;
			$refreshurl = $page->get_itemusageURL($itemID);
			$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);
			$page->body .= $config->twig->render('items/ii/usage/sales-usage.twig', ['page' => $page, 'json' => $json, 'module_json' => $module_json]);
			$page->body .= $config->twig->render('items/ii/usage/warehouses.twig', ['page' => $page, 'json' => $json, 'module_json' => $module_json, 'module_usage' => $module_usage]);
			$page->js = $config->twig->render('items/ii/usage/warehouses.js.twig', ['page' => $page, 'json' => $json, 'module_json' => $module_json, 'module_usage' => $module_usage]);
		} else {
			if ($session->usagetry > 3) {
				$page->headline = $page->title = "Costing File could not be loaded";
				$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->usagetry++;
				$session->redirect($page->get_itemusageURL($itemID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
