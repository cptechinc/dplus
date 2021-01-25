<?php
	include_once('./ii-include.php');

	if ($lookup_ii->lookup_itm($itemID)) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/ii/bread-crumbs.twig', ['page' => $page, 'item' => $item]);
		$page->title = "$itemID Purchase History";

		if ($input->get->date) {
			$date = $input->get->text('date');

			$module_json = $modules->get('JsonDataFiles');
			$json = $module_json->get_file(session_id(), $page->jsoncode);

			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				if ($json['itemid'] != $itemID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->url_itemID($itemID));
				}
				$session->purchasehistorytry = 0;

				$refreshurl = $page->get_itempurchasehistoryURL($itemID, $date);
				$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

				if ($json['error']) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
				} else {
					$module_formatter = new Dplus\ScreenFormatters\Ii\PurchaseHistory();
					$module_formatter->init_formatter();
					$docm = $modules->get('DocumentManagementPo');
					$page->body .= $config->twig->render('items/ii/purchase-history/purchase-history.twig', ['page' => $page, 'itemID' => $itemID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint(), 'docm' => $docm]);
				}
			} else {
				if ($session->purchasehistorytry > 3) {
					$page->headline = $page->title = "Purchase History File could not be loaded";
					$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->purchasehistorytry++;
					$session->redirect($page->get_itempurchasehistoryURL($itemID, $date));
				}
			}
		} else {
			$page->body .= $config->twig->render('items/ii/purchase-history/date-form.twig', ['page' => $page, 'itemID' => $itemID]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
