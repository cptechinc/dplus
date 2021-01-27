<?php
	include_once('./vi-include.php');

	if ($vendorquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('vendors/vi/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);

		if ($input->get->shipfromID) {
			$shipfromID = $input->get->text('shipfromID');
			$load_shipfrom = $load_vendor;

			if ($load_shipfrom->shipfrom_exists()) {
				$shipfrom = $load_shipfrom->get_shipfrom();
				$page->title = "$vendor->name Shipfrom $shipfrom->id Unreleased Purchase Orders";

			} else {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "$vendorID Ship-to $shipfromID does not exist", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Check if shipfromID is correct"]);
			}
		} else {
			$page->title = "$vendor->name Unreleased Purchase Orders";
		}

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['vendid'] != $vendorID || $json['shipid'] != $shipfromID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_viunreleasedpurchaseordersURL($vendorID, $shipfromID));
			}
			$session->unreleasedpurchaseorderstry = 0;

			$module_formatter = new Dplus\ScreenFormatters\Vi\UnreleasedPurchaseOrders();
			$module_formatter->init_formatter();
			$document_management = $modules->get('DocumentManagement');
			$refreshurl = $page->get_viunreleasedpurchaseordersURL($vendorID, $shipfromID);
			$page->body .= $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);
			$page->body .= $config->twig->render('vendors/vi/unreleased/unreleased-purchase-orders.twig', ['page' => $page, 'vendorID' => $vendorID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint(), 'document_management' => $document_management]);
		} else {
			if ($session->unreleasedpurchaseorderstry > 3) {
				$page->headline = $page->title = "Unreleased Purchase Orders File could not be loaded";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->unreleasedpurchaseorderstry++;
				$session->redirect($page->get_viunreleasedpurchaseordersURL($vendorID, $shipfromID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
