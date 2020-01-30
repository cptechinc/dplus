<?php
	include_once('./ii-include.php');

	if ($itemquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/ii/bread-crumbs.twig', ['page' => $page, 'item' => $item]);
		$page->title = "$itemID Sales History";

		if ($input->get->date) {
			$date = $input->get->text('date');
			$page->title .= " starting from $date";

			$module_json = $modules->get('JsonDataFiles');
			$json = $module_json->get_file(session_id(), $page->jsoncode);

			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				if ($json['itemid'] != $itemID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->get_itemsaleshistoryURL($itemID));
				}
				$session->saleshistorytry = 0;

				$refreshurl = $page->get_itemsaleshistoryURL($itemID, $date);
				$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

				if ($json['error']) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
				} else {
					$module_formatter = $modules->get('IiSalesHistory');
					$module_formatter->init_formatter();
					$document_management = $modules->get('DocumentManagement');
					$page->body .= $config->twig->render('items/ii/sales-history/sales-history.twig', ['page' => $page, 'itemID' => $itemID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint(), 'document_management' => $document_management]);
				}
			} else {
				if ($session->saleshistorytry > 3) {
					$page->headline = $page->title = "Sales History File could not be loaded";
					$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->saleshistorytry++;
					$session->redirect($page->get_itemsaleshistoryURL($itemID, $date));
				}
			}
		} else {
			$page->body .= $config->twig->render('items/ii/sales-history/date-form.twig', ['page' => $page, 'itemID' => $itemID]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
