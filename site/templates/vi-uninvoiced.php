<?php
	include_once('./vi-include.php');

	if ($vendorquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('vendors/vi/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);
		$page->title = "$vendor->name Uninvoiced";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['vendid'] != $vendorID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_viuninvoicedURL($vendorID));
			}
			$session->uninvoicedtry = 0;

			$document_management = $modules->get('DocumentManagement');
			$refreshurl = $page->get_viuninvoicedURL($vendorID);
			$page->body .= $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

			if ($json['error']) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
			} else {
				$page->body .= $config->twig->render('vendors/vi/uninvoiced/uninvoiced.twig', ['page' => $page, 'vendorID' => $vendorID, 'json' => $json, 'module_json' => $module_json, 'document_management' => $document_management]);
			}
		} else {
			if ($session->uninvoicedtry > 3) {
				$page->headline = $page->title = "Uninvoiced File could not be loaded";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->uninvoicedtry++;
				$session->redirect($page->get_viuninvoicedURL($vendorID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
