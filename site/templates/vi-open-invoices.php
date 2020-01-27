<?php
	include_once('./vi-include.php');

	if ($vendorquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('vendors/vi/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);
		$page->title = "$vendor->name Open Invoices";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['vendid'] != $vendorID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_viopeninvoicesURL($vendorID));
			}
			$session->openinvoicestry = 0;

			$module_formatter = $modules->get('SfViOpenInvoices');
			$module_formatter->init_formatter();
			$document_management = $modules->get('DocumentManagement');
			$refreshurl = $page->get_viopeninvoicesURL($vendorID);
			$page->body .= $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

			if ($json['error']) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
			} else {
				$page->body .= $config->twig->render('vendors/vi/open-invoices/open-invoices.twig', ['page' => $page, 'vendorID' => $vendorID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint(), 'document_management' => $document_management]);
			}
		} else {
			if ($session->openinvoicestry > 3) {
				$page->headline = $page->title = "Open Invoices File could not be loaded";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->openinvoicestry++;
				$session->redirect($page->get_viopeninvoicesURL($vendorID));
			}
		}
	}


	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
