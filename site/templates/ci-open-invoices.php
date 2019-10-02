<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$page->title = "$custID Open Invoices";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['custid'] != $custID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_customeropeninvoicesURL($custID));
			}
			$session->openinvoicestry = 0;
			$module_formatter = $modules->get('CiOpenInvoices');
			$module_formatter->init_formatter();
			$refreshurl = $page->get_customeropeninvoicesURL($custID);
			$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);
			$page->body .= $config->twig->render('customers/ci/customer/open-invoices.twig', ['page' => $page, 'custID' => $custID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint()]);
		} else {
			if ($session->openinvoicestry > 3) {
				$page->headline = $page->title = "Open Invoices File could not be loaded";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->openinvoicestry++;
				$session->redirect($page->get_customeropeninvoicesURL($custID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
