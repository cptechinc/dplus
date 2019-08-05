<?php
	include_once('./ii-include.php');

	if ($itemquery->count()) {
		$page->title = "$itemID Sales History";

		if ($input->get->date) {
			$date = $input->get->text('date');

			$module_json = $modules->get('JsonDataFiles');
			$json = $module_json->get_file(session_id(), $page->jsoncode);

			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				$session->saleshistorytry = 0;
				$module_formatter = $modules->get('IiSalesHistory');
				$module_formatter->init_formatter();

				$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID]);

				$page->body .= $config->twig->render('items/ii/sales-history/sales-history.twig', ['page' => $page, 'itemID' => $itemID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint()]);

			} else {
				if ($session->saleshistorytry > 3) {
					$page->headline = $page->title = "Sales History File could not be loaded";
					$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->saleshistorytry++;
					$session->redirect($page->get_itemsaleshistoryURL($itemID, $date));
				}
			}
		} else {
			$page->body = $config->twig->render('items/ii/sales-history/date-form.twig', ['page' => $page, 'itemID' => $itemID]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
