<?php
	include_once('./vi-include.php');

	if ($vendorquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('vendors/vi/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);
		$page->title = "$vendor->name Payments";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['vendid'] != $vendorID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_vipaymentsURL($vendorID));
			}
			$session->paymentstry = 0;

			$document_management = $modules->get('DocumentManagement');
			$refreshurl = $page->get_vipaymentsURL($vendorID);
			$page->body .= $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);
			$page->body .= $config->twig->render('vendors/vi/payments/payments.twig', ['page' => $page, 'vendorID' => $vendorID, 'json' => $json, 'document_management' => $document_management]);
		} else {
			if ($session->paymentstry > 3) {
				$page->headline = $page->title = "Payments File could not be loaded";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->paymentstry++;
				$session->redirect($page->get_vipaymentsURL($vendorID, $shipfromID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
